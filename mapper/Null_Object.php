<?php
namespace SAF\Framework\Mapper;

use SAF\Framework\Builder;
use SAF\Framework\Reflection\Reflection_Class;

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
			if (!$property->isStatic()) {
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
	 * @param $object     object
	 * @param $class_name string you can set a class name of a parent class to get a partial isNull()
	 * @return boolean
	 */
	public static function isNull($object, $class_name = null)
	{
		if (!isset($object)) {
			return true;
		}
		if (!isset($class_name)) {
			$class_name = get_class($object);
		}
		$is_null = true;
		$getter_ignore = Getter::$ignore;
		Getter::$ignore = true;
		foreach ((new Reflection_Class($class_name))->accessProperties() as $property) {
			if (!$property->isStatic() && !$property->getAnnotation('composite')->value) {
				$value = $property->getValue($object);
				if (
					(is_object($value) && !self::isNull($value))
					|| (!is_object($value) && !is_null($value))
				) {
					$is_null = false;
					break;
				}
			}
		}
		Getter::$ignore = $getter_ignore;
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
