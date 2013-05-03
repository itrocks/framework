<?php
namespace SAF\Framework;

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
		return !get_object_vars($object);
	}

	//----------------------------------------------------------------------------------- newInstance
	/**
	 * Returns a new instance of a search-formatter object of given class
	 *
	 * This creates an object with unset properties, as only set properties are used for searches.
	 *
	 * @param $class_name string
	 * @return object
	 */
	public static function create($class_name)
	{
		$object = Builder::create($class_name);
		$class = Reflection_Class::getInstanceOf($class_name);
		$class->accessProperties();
		foreach (array_keys(get_object_vars($object)) as $property_name) {
			unset($object->$property_name);
		}
		$class->accessPropertiesDone();
		return $object;
	}

}
