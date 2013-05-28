<?php
namespace SAF\Framework;

/**
 * Search objects are used for searches : all public properties are unset, as unset properties are
 * not used for DAO search functions.
 * Public properties are unset.
 * Null-value properties will be used if properties are private or protected, as they cant be unset.
 */
abstract class Search_Object extends Null_Object
{

	//---------------------------------------------------------------------------------------- create
	/**
	 * Returns a new instance of a search-formatted object of given class
	 *
	 * This creates an object with unset properties, as only set properties are used for searches.
	 * Private or protected properties can't be unset : they are kept with a null value.
	 *
	 * @param $class_name string
	 * @return object
	 */
	public static function create($class_name)
	{
		$object = Builder::create($class_name);
		$class = Reflection_Class::getInstanceOf($class_name);
		foreach ($class->accessProperties() as $property) {
			if (!$property->isStatic()) {
				if ($property->isPublic()) {
					$name = $property->name;
					unset($object->$name);
				}
				else {
					$property->setValue($object, null);
				}
			}
		}
		$class->accessPropertiesDone();
		return $object;
	}

}
