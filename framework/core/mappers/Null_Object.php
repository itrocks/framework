<?php
namespace SAF\Framework;

/**
 * A null object is an object which all properties have a value equals to null
 */
abstract class Null_Object
{

	//---------------------------------------------------------------------------------------- isNull
	/**
	 * Returns true if the object has no set properties (ie was created with Null_Object:create())
	 *
	 * @param $object
	 * @return bool
	 */
	public static function isNull($object)
	{
		$is_null = true;
		$class = Reflection_Class::getInstanceOf($object);
		foreach ($class->accessProperties() as $property) {
			if ((!$property->isStatic()) && ($property->getValue($object) !== null)) {
				$is_null = false;
				break;
			}
		}
		$class->accessPropertiesDone();
		return $is_null;
	}

	//----------------------------------------------------------------------------------- newInstance
	/**
	 * Returns a new instance of an object, but sets all its properties values to null
	 *
	 * @param $class_name string
	 * @return object
	 */
	public static function create($class_name)
	{
		$object = Builder::create($class_name);
		$class = Reflection_Class::getInstanceOf($class_name);
		foreach ($class->accessProperties() as $property) {
			if(!$property->isStatic()) {
				$property->setValue($object, null);
			}
		}
		$class->accessPropertiesDone();
		return $object;
	}

}
