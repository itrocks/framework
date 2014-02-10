<?php
namespace SAF\Framework;

/**
 * Getter default methods are common getters for Dao linked objects
 */
abstract class Getter
{

	//--------------------------------------------------------------------------------------- $ignore
	/**
	 * @var boolean
	 */
	public static $ignore = false;

	//---------------------------------------------------------------------------------------- getAll
	/**
	 * Generic getter for getting all objects of a given class
	 *
	 * @param $value             object[]
	 * @param $element_type_name string
	 */
	public static function getAll(&$value, $element_type_name)
	{
		if (!(self::$ignore || isset($value))) {
			$value = Dao::readAll($element_type_name, Dao::sort());
		}
	}

	//--------------------------------------------------------------------------------- getCollection
	/**
	 * Generic getter for a collection of objects
	 *
	 * @param $value      Component[] Actual value of the property (will be returned if not null)
	 * @param $class_name string Class for each collection's object
	 * @param $object     object Parent object
	 * @param $property   string|Reflection_Property Parent property (or property name). Recommended but can be ommited if foreign class is a Component
	 */
	public static function getCollection(&$value, $class_name, $object, $property = null)
	{
		if (!(self::$ignore || isset($value))) {
			if (Dao::getObjectIdentifier($object)) {
				$search_element = Search_Object::create($class_name);
				$is_component = class_uses_trait($search_element, 'SAF\Framework\Component');
				if (isset($property)) {
					if (!$property instanceof Reflection_Property) {
						$property = new Reflection_Property(get_class($object), $property);
					}
					$property_name = $property->getAnnotation("foreign")->value;
					$dao = ($dao = $property->getAnnotation("dao")->value)
						? Dao::get($dao) : Dao::current();
				}
				else {
					$dao = Dao::current();
					$property_name = null;
				}
				if ($is_component) {
					/** @var $search_element Component */
					$search_element->setComposite($object, $property_name);
					$value = $dao->search($search_element, null, Dao::sort());
				}
				// when element class is not a component and a property name was found
				elseif (!empty($property_name)) {
					$property = new Reflection_Property(get_class($search_element), $property_name);
					$accessible = $property->isPublic();
					if (!$accessible) {
						$property->setAccessible(true);
					}
					$property->setValue($search_element, $object);
					if (!$accessible) {
						$property->setAccessible(false);
					}
					$value = $dao->search($search_element, null, Dao::sort());
				}
				else {
					trigger_error(
						"getCollection() must be called for a component foreign type"
						. " or with a parent property name",
						E_USER_ERROR
					);
				}
			}
			if (!isset($value)) {
				$value = array();
			}
		}
	}

	//----------------------------------------------------------------------------------- getDateTime
	/**
	 * Register this for any Date_Time property using "@link DateTime" annotation
	 *
	 * @param $value Date_Time|string
	 */
	public function getDateTime(&$value)
	{
		if (is_string($value)) {
			$value = Date_Time::fromISO($value);
		}
	}

	//---------------------------------------------------------------------------------------- getMap
	/**
	 * Generic getter for mapped objects
	 *
	 * @param $value    Component[] actual value of the property (will be returned if not null)
	 * @param $object   object the parent object
	 * @param $property string|Reflection_Property the source property (or name) for map reading
	 */
	public static function getMap(&$value, $object, $property)
	{
		if (!(self::$ignore || isset($value))) {
			if (Dao::getObjectIdentifier($object)) {
				if (!($property instanceof Reflection_Property)) {
					$property = new Reflection_Property(get_class($object), $property);
				}
				$dao = ($dao = $property->getAnnotation("dao")->value) ? Dao::get($dao) : Dao::current();
				$value = $dao->search(
					array(get_class($object) . "->" . $property->name => $object),
					Builder::className($property->getType()->getElementTypeAsString()),
					Dao::sort()
				);
			}
			else {
				$value = array();
			}
		}
	}

	//------------------------------------------------------------------------------------- getObject
	/**
	 * Generic getter for an object
	 *
	 * @param $value       mixed actual value of the object, or identifier to an object, or null
	 * @param $class_name string the object class name
	 * @param $object     object the parent object
	 * @param $property   string|Reflection_Property the parent property
	 */
	public static function getObject(&$value, $class_name, $object = null, $property = null)
	{
		if (!(self::$ignore || is_object($value))) {
			if ($property instanceof Reflection_Property) {
				$property_name = $property->name;
			}
			elseif (is_string($property) && is_object($object)) {
				$property_name = $property;
				$property = new Reflection_Property(get_class($object), $property_name);
			}
			if (is_object($object) && isset($property_name)) {
				$id_property_name = "id_" . $property_name;
				if (isset($object->$id_property_name)) {
					$value = $object->$id_property_name;
				}
			}
			if (isset($value)) {
				$value = (isset($property) && ($dao = $property->getAnnotation("dao")->value))
					? Dao::get($dao)->read($value, $class_name)
					: Dao::read($value, $class_name);
			}
		}
	}

}
