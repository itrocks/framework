<?php
namespace SAF\Framework;

abstract class Search_Object
{

	//----------------------------------------------------------------------------------- newInstance
	/**
	 * Returns a new instance of a search-formatter object of given class
	 *
	 * This creates an object with unset properties, as only set properties are used for searches.
	 *
	 * @param string $class_name
	 * @return object
	 */
	public static function newInstance($class_name)
	{
		$class = Reflection_Class::getInstanceOf($class_name);
		$object = new $class_name();
		foreach ($class->accessProperties() as $property) {
			$property_name = $property->name;
			unset($object->$property_name);
		}
		$class->accessPropertiesDone();
		return $object;
	}

}
