<?php
namespace ITRocks\Framework\Mapper;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Tools\Date_Time;

/**
 * A null object is an object which all properties have a null value
 */
abstract class Null_Object
{

	//---------------------------------------------------------------------------------------- create
	/**
	 * Returns a new instance of an object, but sets all its properties values to null
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name class-string<T>
	 * @return T
	 * @template T
	 */
	public static function create(string $class_name) : object
	{
		/** @noinspection PhpUnhandledExceptionInspection $class_name must be valid */
		$object = Builder::create($class_name);
		/** @noinspection PhpUnhandledExceptionInspection $class_name must be valid */
		foreach ((new Reflection_Class($class_name))->accessProperties() as $property) {
			if (!$property->isStatic()) {
				$property->setValue($object, null);
			}
		}
		return $object;
	}

	//--------------------------------------------------------------------------------------- isEmpty
	/**
	 * Returns true if the object has only empty or default properties
	 *
	 * @noinspection PhpDocMissingThrowsInspection
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
		$getter_ignore = Getter::$ignore;
		$is_empty      = true;
		/** @noinspection PhpUnhandledExceptionInspection $class_name must be valid */
		foreach ((new Reflection_Class($class_name))->accessProperties() as $property) {
			if (
				!$property->isStatic()
				&& !$property->getAnnotation('composite')->value
				&& !$property->getAnnotation('link_composite')->value
				&& $property->getAnnotation('empty_check')->value
			) {
				Getter::$ignore = true;
				/** @noinspection PhpUnhandledExceptionInspection $property from $object and accessible */
				$value          = $property->getValue($object);
				Getter::$ignore = $getter_ignore;
				if (
					(is_object($value) && !self::isEmpty($value))
					|| (!is_object($value) && !$property->isValueEmptyOrDefault($value))
				) {
					$is_empty = false;
					break;
				}
				$property_type = $property->getType();
				if ($property_type->isClass() && !$property_type->isMultiple()) {
					$id_property = 'id_' . $property->name;
					if (isset($object->$id_property) && $object->$id_property) {
						$is_empty = false;
						break;
					}
				}
			}
		}
		return $is_empty;
	}

	//---------------------------------------------------------------------------------------- isNull
	/**
	 * Returns true if the object has no set properties (ie was created with Null_Object:create())
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object            object
	 * @param $properties_filter callable set a callback function that filters properties to be tested
	 * @return boolean
	 */
	public static function isNull($object, callable $properties_filter = null)
	{
		if (!isset($object)) {
			return true;
		}
		$is_null = true;
		/** @noinspection PhpUnhandledExceptionInspection Class of an object is always valid */
		$properties = (new Reflection_Class($object))->accessProperties();
		if ($properties_filter) {
			$properties = call_user_func($properties_filter, $properties);
		}
		$getter_ignore = Getter::$ignore;
		foreach ($properties as $property) {
			if (
				!$property->isStatic()
				&& !$property->getAnnotation('composite')->value
				&& $property->getAnnotation('empty_check')->value
			) {
				Getter::$ignore = true;
				/** @noinspection PhpUnhandledExceptionInspection $property from $object and accessible */
				$value          = $property->getValue($object);
				Getter::$ignore = $getter_ignore;
				if (
					(($value instanceof Date_Time) && !$value->isEmpty())
					|| (is_object($value) && !self::isNull($value))
					|| (!is_object($value) && !is_null($value))
				) {
					$is_null = false;
					break;
				}
				$property_type = $property->getType();
				if ($property_type->isClass() && !$property_type->isMultiple()) {
					$id_property = 'id_' . $property->name;
					if (isset($object->$id_property) && !is_null($object->$id_property)) {
						$is_null = false;
						break;
					}
				}
			}
		}
		return $is_null;
	}

}
