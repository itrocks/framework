<?php
namespace ITRocks\Framework\Mapper;

use Exception;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\PHP\Dependency;
use ITRocks\Framework\Reflection\Annotation\Property\Foreign_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Store_Annotation;
use ITRocks\Framework\Reflection\Link_Class;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Sql\Builder\Link_Property_Name;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Tools\Date_Time_Error;

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

	//------------------------------------------------------------------------- getAbstractCollection
	/**
	 * Gets a collection of objects which class is abstract.
	 * - Use Dependency to get all possible classes
	 * - Get objects from each possible class
	 * - Return all of them
	 *
	 * @param $class_name string
	 * @param $object     object
	 * @param $property   string|Reflection_Property
	 * @return object[]
	 */
	private static function getAbstractCollection($class_name, $object, $property = null)
	{
		$objects = [];
		$class_names = self::getFinalClasses($class_name);
		foreach ($class_names as $class_name) {
			$stored = null;
			self::getCollection($stored, $class_name, $object, $property);
			$objects = array_merge($objects, $stored);
		}
		return $objects;
	}

	//---------------------------------------------------------------------------------------- getAll
	/**
	 * Generic getter for getting all objects of a given class
	 *
	 * @param $stored            object[]
	 * @param $element_type_name string
	 * @return object[]
	 */
	public static function & getAll(array &$stored = null, $element_type_name)
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
	 * @param $stored     Component[]|string Actual value of the property (will be returned if not null)
	 * @param $class_name string Class for each collection's object
	 * @param $object     object Parent object
	 * @param $property   string|Reflection_Property Parent property (or property name). Recommended
	 *        but can be omitted if foreign class is a Component
	 * @return object[]
	 */
	public static function & getCollection(
		&$stored = null, $class_name, $object, $property = null
	) {
		// TODO JSON will work only if $property is set. Should add string / null case
		if (
			!self::$ignore
			&& ($property instanceof Reflection_Property)
			&& Store_Annotation::of($property)->isJson()
		) {
			if (isset($stored) && is_string($stored)) {
				$objects_arrays = json_decode($stored, true);
				$stored         = [];
				if ($objects_arrays) {
					foreach ($objects_arrays as $key => $object_array) {
						$stored[$key] = static::schemaDecode($object_array, $property);
					}
				}
			}
		}
		elseif (!(self::$ignore || isset($stored))) {
			if (Dao::getObjectIdentifier($object)) {
				$class_name = Builder::className($class_name);
				$class      = new Reflection_Class($class_name);
				if ($class->isAbstract()) {
					$stored = self::getAbstractCollection($class_name, $object, $property);
				}
				else {
					$search_element = Search_Object::create($class_name);
					$is_component   = isA($search_element, Component::class);
					if ($property) {
						if (!($property instanceof Reflection_Property)) {
							$property = new Reflection_Property(get_class($object), $property);
						}
						$property_name = Foreign_Annotation::of($property)->value;
						$dao           = Dao::get($property->getAnnotation('dao')->value);
					}
					else {
						$dao           = Dao::current();
						$property_name = null;
					}
					if ($is_component) {
						/** @var $search_element Component */
						$search_element->setComposite($object, $property_name);
						$link_properties_names = (new Link_Class($class_name))->getUniquePropertiesNames();
						$options               = [Dao::sort()];
						if ($property_name) {
							$options[] = new Link_Property_Name($property_name);
						}
						if ($link_properties_names) {
							$options[] = Dao::key($link_properties_names);
						}
						$stored = $dao->search($search_element, null, $options);
					}
					// when element class is not a component and a property name was found
					elseif ($property_name) {
						$property   = new Reflection_Property(get_class($search_element), $property_name);
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
					if ($stored && $is_component) {
						// $element->setComposite() is not used for optimization reason :
						// this should go as fast as it can
						/** @var $element Component */
						$element              = reset($stored);
						$composite_properties = $element->getCompositeProperties($object);
						foreach ($stored as $element) {
							foreach ($composite_properties as $property) {
								$id_property = 'id_' . $property->name;
								if (intval($element->$id_property) === intval($object->id)) {
									$property->setValue($element, $object);
								}
							}
						}
					}
				}
			}
			if (!isset($stored)) {
				$stored = [];
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
			try {
				$stored = Date_Time::fromISO($stored);
			}
			catch (Exception $exception) {
				$stored = Date_Time_Error::fromError($stored);
			}
		}
		return $stored;
	}

	//------------------------------------------------------------------------------- getFinalClasses
	/**
	 * Gets final class names of an extensible class
	 * This uses Dependency cache
	 *
	 * @param $class_name string
	 * @return string[]
	 */
	private static function getFinalClasses($class_name)
	{
		$class_names = [];
		$search = ['dependency_name' => $class_name, 'type' => Dependency::T_EXTENDS];
		foreach (Dao::search($search, Dependency::class) as $dependency) {
			/** @var $dependency Dependency */
			$class = new Reflection_Class($dependency->class_name);
			if (!$class->isAbstract()) {
				$class_names[$class->name] = $class->name;
			}
			$class_names = array_merge($class_names, self::getFinalClasses($class->name));
		}
		return $class_names;
	}

	//---------------------------------------------------------------------------------------- getMap
	/**
	 * Generic getter for mapped objects
	 *
	 * @param $stored   object[] actual value of the property (will be returned if not null)
	 * @param $object   object the parent object
	 * @param $property string|Reflection_Property the source property (or name) for map reading
	 * @return Component[]
	 */
	public static function & getMap(array &$stored = null, $object, $property)
	{
		if (!(self::$ignore || isset($stored))) {
			if (Dao::getObjectIdentifier($object)) {
				if (!($property instanceof Reflection_Property)) {
					$property = new Reflection_Property(get_class($object), $property);
				}
				$dao               = Dao::get($property->getAnnotation('dao')->value);
				$class_name        = get_class($object);
				$linked_class_name = (new Link_Class($class_name))->getLinkedClassName();
				if ($linked_class_name) {
					$object     = (new Link_Class($class_name))->getCompositeProperty()->getValue($object);
					$class_name = $linked_class_name;
				}
				$element_type = $property->getType()->getElementType();
				$is_abstract  = $element_type->asReflectionClass()->isAbstract();
				$sort         = $is_abstract ? Dao::sort(['id']) : Dao::sort();
				$stored       = $dao->search(
					[$class_name . '->' . $property->name => $object], $element_type->asString(), [$sort]
				);
				if ($is_abstract) {
					$sort->sortObjects($stored);
				}
			}
			else {
				$stored = [];
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
				$property      = new Reflection_Property(get_class($object), $property_name);
			}
			if ($property && $property->getAnnotation('component')->value) {
				$foreign_property_name = Foreign_Annotation::of($property)->value;
				if ($foreign_property_name && Dao::getObjectIdentifier($object)) {
					$stored = Dao::searchOne(
						[$foreign_property_name => $object], $property->getType()->getElementTypeAsString()
					);
				}
			}
			elseif (is_object($object) && isset($property_name)) {
				$id_property_name = 'id_' . $property_name;
				if (isset($object->$id_property_name)) {
					$stored = $object->$id_property_name;
				}
			}
			if (isset($stored) && !is_object($stored)) {
				if (
					isset($property)
					&& Store_Annotation::of($property)->value
					&& !Store_Annotation::of($property)->isFalse()
				) {
					if (Store_Annotation::of($property)->isGz()) {
						/** @noinspection PhpUsageOfSilenceOperatorInspection if not deflated */
						$inflated = @gzinflate($stored);
						if ($inflated !== false) {
							$stored = $inflated;
						}
					}
					switch (Store_Annotation::of($property)->value) {
						case Store_Annotation::JSON:
							$stored = json_decode($stored, true);
							$stored = static::schemaDecode($stored, $property);
							break;
						default:
							$stored = call_user_func([$property->getType()->asString(), 'fromString'], $stored);
							break;
					}
				}
				else {
					$class_name = Builder::className($class_name);
					$stored     = isset($property)
						? Dao::get($property->getAnnotation('dao')->value)->read($stored, $class_name)
						: Dao::read($stored, $class_name);
				}
			}
		}
		return $stored;
	}

	//---------------------------------------------------------------------------------------- ignore
	/**
	 * Changes the state of self::$ignore
	 *
	 * @example
	 * $getter_ignore = Getter::ignore(true);
	 * // .. do some stuff
	 * Getter::ignore($getter_ignore);
	 * @param $ignore boolean new state for self::$ignore
	 * @return boolean old state of self::$ignore
	 */
	public static function ignore($ignore)
	{
		$result       = self::$ignore;
		self::$ignore = $ignore;
		return $result;
	}

	//---------------------------------------------------------------------------------- schemaDecode
	/**
	 * @param $stored   array The object stored into an array : [$property_name => $value]
	 * @param $property Reflection_Property
	 * @return object
	 */
	private static function schemaDecode(array $stored, Reflection_Property $property)
	{
		$stored_array = $stored;
		$class_name   = '';
		if (
			isset($stored_array[Store_Annotation::JSON_CLASS])
			&& $stored_array[Store_Annotation::JSON_CLASS]
		) {
			$class_name = $stored_array[Store_Annotation::JSON_CLASS];
			unset($stored_array[Store_Annotation::JSON_CLASS]);
		}
		else if ($property->getType()->isClass()) {
			$class_name = $property->getType()->getElementTypeAsString();
		}
		if ($class_name) {
			/** @var $arrays_of_objects array object[$property_name][$key] */
			$arrays_of_objects = [];
			foreach ($stored_array as $property_name => $stored_value) {
				if (is_array($stored_value)) {
					$property = new Reflection_Property($class_name, $property_name);
					if ($property->getType()->isClass() && $property->getType()->isMultiple()) {
						$property_class_name = $property->getType()->getElementTypeAsString();
						foreach ($stored_value as $key => $object_identifier) {
							if (is_integer($object_identifier)) {
								if (!isset($arrays_of_objects[$property_name])) {
									$arrays_of_objects[$property_name] = [];
								}
								$arrays_of_objects[$property_name][$key] = Dao::read(
									$object_identifier, $property_class_name
								);
							}
						}
					}
				}
			}
			foreach ($arrays_of_objects as $property_name => $value) {
				unset($stored_array[$property_name]);
			}
			$stored = Builder::fromArray($class_name, $stored_array);
			foreach ($arrays_of_objects as $property_name => $value) {
				$stored->$property_name = $value;
			}
		}
		return $stored;
	}

}
