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
	 * @param $stored            object[]
	 * @param $element_type_name string
	 * @return object[]
	 */
	public static function & getAll(&$stored, $element_type_name)
	{
		if (!(self::$ignore || isset($stored))) {
			$stored = Dao::readAll($element_type_name, Dao::sort());
		}
		return $stored;
	}

	//--------------------------------------------------------------------------------- getCollection
	/**
	 * Generic getter for a collection of objects
	 *
	 * @param $stored     Component[] Actual value of the property (will be returned if not null)
	 * @param $class_name string Class for each collection's object
	 * @param $object     object Parent object
	 * @param $property   string|Reflection_Property Parent property (or property name). Recommended
	 *        but can be omitted if foreign class is a Component
	 * @return object[]
	 */
	public static function & getCollection(&$stored, $class_name, $object, $property = null)
	{
		if (!(self::$ignore || isset($stored))) {
			if (Dao::getObjectIdentifier($object)) {
				$search_element = Search_Object::create($class_name);
				$is_component = isA($search_element, Component::class);
				if (isset($property)) {
					if (!$property instanceof Reflection_Property) {
						$property = new Reflection_Property(get_class($object), $property);
					}
					$property_name = $property->getAnnotation('foreign')->value;
					$dao = ($dao = $property->getAnnotation('dao')->value)
						? Dao::get($dao) : Dao::current();
				}
				else {
					$dao = Dao::current();
					$property_name = null;
				}
				if ($is_component) {
					/** @var $search_element Component */
					$search_element->setComposite($object, $property_name);
					$stored = $dao->search($search_element, null, Dao::sort());
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
					$stored = $dao->search($search_element, null, Dao::sort());
				}
				else {
					trigger_error(
						'getCollection() must be called for a component foreign type'
						. ' or with a parent property name',
						E_USER_ERROR
					);
				}
			}
			if (!isset($stored)) {
				$stored = array();
			}
		}
		return $stored;
	}

	//----------------------------------------------------------------------------------- getDateTime
	/**
	 * Register this for any Date_Time property using '@link DateTime' annotation
	 *
	 * @param $stored Date_Time|string
	 * @return Date_Time
	 */
	public static function getDateTime(&$stored)
	{
		if (is_string($stored)) {
			$stored = Date_Time::fromISO($stored);
		}
		return $stored;
	}

	//---------------------------------------------------------------------------------------- getMap
	/**
	 * Generic getter for mapped objects
	 *
	 * @param $stored   Component[] actual value of the property (will be returned if not null)
	 * @param $object   object the parent object
	 * @param $property string|Reflection_Property the source property (or name) for map reading
	 * @return Component[]
	 */
	public static function & getMap(&$stored, $object, $property)
	{
		if (!(self::$ignore || isset($stored))) {
			if (Dao::getObjectIdentifier($object)) {
				if (!($property instanceof Reflection_Property)) {
					$property = new Reflection_Property(get_class($object), $property);
				}
				$dao = ($dao = $property->getAnnotation('dao')->value) ? Dao::get($dao) : Dao::current();
				$class_name = get_class($object);
				$link_class_name = (new Link_Class($class_name))->getLinkClassName();
				if ($link_class_name) {
					$object = (new Link_Class($class_name))->getCompositeProperty()->getValue($object);
					$class_name = $link_class_name;
				}
				$stored = $dao->search(
					array($class_name . '->' . $property->name => $object),
					$property->getType()->getElementTypeAsString(),
					Dao::sort()
				);
			}
			else {
				$stored = array();
			}
		}
		return $stored;
	}

	//------------------------------------------------------------------------------------- getObject
	/**
	 * Generic getter for an object
	 *
	 * @param $stored     mixed actual value of the object, or identifier to an object, or null
	 * @param $class_name string the object class name
	 * @param $object     object the parent object
	 * @param $property   string|Reflection_Property the parent property
	 * @return object
	 */
	public static function getObject(&$stored, $class_name, $object = null, $property = null)
	{
		if (!(self::$ignore || is_object($stored))) {
			if ($property instanceof Reflection_Property) {
				$property_name = $property->name;
			}
			elseif (is_string($property) && is_object($object)) {
				$property_name = $property;
				$property = new Reflection_Property(get_class($object), $property_name);
			}
			if (is_object($object) && isset($property_name)) {
				$id_property_name = 'id_' . $property_name;
				if (isset($object->$id_property_name)) {
					$stored = $object->$id_property_name;
				}
			}
			if (isset($stored)) {
				$stored = (isset($property) && ($dao = $property->getAnnotation('dao')->value))
					? Dao::get($dao)->read($stored, $class_name)
					: Dao::read($stored, $class_name);
			}
		}
		return $stored;
	}

}
