<?php
namespace ITRocks\Framework\Mapper;

use Exception;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Type;
use ITRocks\Framework\Tools\Can_Be_Empty;
use ReflectionException;

/**
 * An empty object is an object which all properties have an empty or default value
 */
abstract class Empty_Object
{

	//------------------------------------------------------------------------------------------ CAST
	const CAST = [
		Type::_ARRAY  => [],
		Type::BOOLEAN => false,
		Type::FLOAT   => 0,
		Type::INTEGER => 0,
		Type::STRING  => ''
	];

	//---------------------------------------------------------------------------------------- create
	/**
	 * Returns a new instance of an object, but sets all its properties values to empty
	 *
	 * The empty value depends on the type of the property, simple type get an empty value of the
	 * same type. Object, resource, callable properties get an empty value of null.
	 *
	 * @param $class_name string
	 * @return object
	 * @throws ReflectionException
	 * @throws Exception
	 */
	public static function create($class_name)
	{
		$object = Builder::create($class_name);
		foreach ((new Reflection_Class($class_name))->accessProperties() as $property) {
			if (!$property->isStatic()) {
				$type_string = $property->getType()->asString();
				$value = array_key_exists($type_string, static::CAST) ? static::CAST[$type_string] : null;
				$property->setValue($object, $value);
			}
		}
		return $object;
	}

	//--------------------------------------------------------------------------------------- isEmpty
	/**
	 * Returns true if the object properties values are all empty (or null or unset or equal to
	 * default value) or empty objects.
	 *
	 * @param $object          object
	 * @param $check_composite boolean if true, check if @composite properties are empty too
	 * @return boolean
	 */
	public static function isEmpty($object, $check_composite = false)
	{
		$is_empty = true;
		if ($object instanceof Can_Be_Empty) {
			$is_empty = $object->isEmpty();
		}
		else {
			/** @noinspection PhpUnhandledExceptionInspection Class of an object is always valid */
			$class   = new Reflection_Class(get_class($object));
			$default = get_class_vars($class->name);
			foreach ($class->accessProperties() as $property) {
				$is_composite = $property->getAnnotation('composite')->value;
				if (
					!$property->isStatic()
					&& ($check_composite || !$is_composite)
					&& $property->getAnnotation('empty_check')->value
				) {
					$value = $property->getValue($object);
					if (
						!empty($value)
						&& (
							(!is_object($value))
							|| (($check_composite && $is_composite) || !Empty_Object::isEmpty($value))
						)
						&& (is_object($value) || ($value !== $default[$property->name]))
					) {
						$is_empty = false;
						break;
					}
				}
			}
		}
		return $is_empty;
	}

}
