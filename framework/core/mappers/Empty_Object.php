<?php
namespace SAF\Framework;

/**
 * An empty object is an object which all properties have an empty or default value
 */
abstract class Empty_Object
{

	//--------------------------------------------------------------------------------------- isEmpty
	/**
	 * Returns true if the object properties values are all empty (or null or unset or equal to
	 * default value) or empty objects.
	 *
	 * @param $object object
	 * @return boolean
	 */
	public static function isEmpty($object)
	{
		$is_empty = true;
		$class = new Reflection_Class(get_class($object));
		$default = get_class_vars($class->name);
		foreach ($class->accessProperties() as $property) {
			if (!$property->isStatic()) {
				$value = $property->getValue($object);
				if (
					!empty($value)
					&& ((!is_object($value)) || !Empty_Object::isEmpty($value))
					&& (is_object($value) || ($value !== $default[$property->name]))
				) {
					$is_empty = false;
					break;
				}
			}
		}
		return $is_empty;
	}

	//---------------------------------------------------------------------------------------- create
	/**
	 * Returns a new instance of an object, but sets all its properties values to empty
	 *
	 * The empty value depends on the type of the property, simple type get an empty value of the
	 * same type. Object, resource, callable properties get an empty value of null.
	 *
	 * @param $class_name string
	 * @return object
	 */
	public static function create($class_name)
	{
		$object = Builder::create($class_name);
		foreach ((new Reflection_Class($class_name))->accessProperties() as $property) {
			if (!$property->isStatic()) {
				switch ($property->getType()->asString()) {
					case "integer":
					case "float":   $value = 0;       break;
					case "string":  $value = "";      break;
					case "boolean": $value = false;   break;
					case "array":   $value = array(); break;
					default:        $value = null;
				}
				$property->setValue($object, $value);
			}
		}
		return $object;
	}

}
