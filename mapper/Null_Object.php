<?php
namespace ITRocks\Framework\Mapper;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Tools\Date_Time;

/**
 * A null object is an object which all properties have a null value
 */
abstract class Null_Object
{

	//--------------------------------------------------------------------------------------- isEmpty
	/**
	 * Returns true if the object has only empty or default properties
	 *
	 * @param $object     object
	 * @param $class_name string you can set a class name of a parent class to get a partial isNull()
	 * @return boolean
	 */
	public static function isEmpty($object, $class_name = null)
	{
		if (empty($object)) {
			return true;
		}
		if (!isset($class_name)) {
			$class_name = get_class($object);
		}
		$is_empty = true;
		$getter_ignore = Getter::$ignore;
		Getter::$ignore = true;
		foreach ((new Reflection_Class($class_name))->accessProperties() as $property) {
			if (!$property->isStatic() && !$property->getAnnotation('composite')->value) {
				$value = $property->getValue($object);
				if (
					(is_object($value) && !self::isEmpty($value))
					|| (!is_object($value) && !$property->isValueEmptyOrDefault($value))
				) {
					$is_empty = false;
					break;
				}
			}
		}
		Getter::$ignore = $getter_ignore;
		return $is_empty;
	}

	//---------------------------------------------------------------------------------------- isNull
	/**
	 * Returns true if the object has no set properties (ie was created with Null_Object:create())
	 *
	 * @param $object            object
	 * @param $properties_filter callable set a callback function that filters properties to be tested
	 * @return boolean
	 */
	public static function isNull($object, callable $properties_filter = null)
	{
		if (!isset($object)) {
			return true;
		}
		$getter_ignore = Getter::ignore(true);
		$is_null       = true;
		/** @var $properties Reflection_Property[] */
		$properties = (new Reflection_Class(get_class($object)))->accessProperties();
		if ($properties_filter) {
			$properties = call_user_func($properties_filter, $properties);
		}
		foreach ($properties as $property) {
			if (!$property->isStatic() && !$property->getAnnotation('composite')->value) {
				$value = $property->getValue($object);
				if (
					(($value instanceof Date_Time) && !$value->isEmpty())
					|| (is_object($value) && !self::isNull($value))
					|| (!is_object($value) && !is_null($value))
				) {
					$is_null = false;
					break;
				}
			}
		}
		Getter::ignore($getter_ignore);
		return $is_null;
	}

	//---------------------------------------------------------------------------------------- create
	/**
	 * Returns a new instance of an object, but sets all its properties values to null
	 *
	 * @param $class_name string
	 * @return object
	 */
	public static function create($class_name)
	{
		$object = Builder::create($class_name);
		foreach ((new Reflection_Class($class_name))->accessProperties() as $property) {
			if (!$property->isStatic()) {
				$property->setValue($object, null);
			}
		}
		return $object;
	}

}
