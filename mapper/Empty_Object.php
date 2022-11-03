<?php
namespace ITRocks\Framework\Mapper;

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
	 */
	public static function create(string $class_name) : object
	{
		$object = Builder::create($class_name);
		foreach ((new Reflection_Class($class_name))->getProperties() as $property) {
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
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object          object
	 * @param $check_composite boolean if true, check if @composite properties are empty too
	 * @return boolean
	 */
	public static function isEmpty(object $object, bool $check_composite = false) : bool
	{
		$is_empty = true;
		if ($object instanceof Can_Be_Empty) {
			$is_empty = $object->isEmpty();
		}
		else {
			/** @noinspection PhpUnhandledExceptionInspection object */
			$class   = new Reflection_Class($object);
			$default = get_class_vars($class->name);
			foreach ($class->getProperties() as $property) {
				$is_composite = $property->getAnnotation('composite')->value
					|| $property->getAnnotation('link_composite')->value;
				if (
					!$property->isStatic()
					&& ($check_composite || !$is_composite)
					&& $property->getAnnotation('empty_check')->value
				) {
					/** @noinspection PhpUnhandledExceptionInspection $property from $object and accessible */
					$value = $property->getValue($object);
					if (
						!empty($value)
						&& (
							(!is_object($value))
							|| (($check_composite && $is_composite) || !Empty_Object::isEmpty($value))
						)
						&& (
							is_object($value)
							|| (
								array_key_exists($property->name, $default)
									? ($value !== $default[$property->name])
									: $value
							)
						)
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
