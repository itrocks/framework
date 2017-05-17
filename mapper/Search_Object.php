<?php
namespace ITRocks\Framework\Mapper;

use ITRocks\Framework\AOP;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Reflection\Reflection_Class;

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
	 * @param $class_name     string
	 * @param $deactivate_aop boolean true to disable AOP on properties for the search object
	 * @return object
	 */
	public static function create($class_name, $deactivate_aop = false)
	{
		$object = Builder::create($class_name);
		foreach ((new Reflection_Class(get_class($object)))->accessProperties() as $property) {
			if (!$property->isStatic()) {
				if ($property->isPublic()) {
					$name = $property->name;
					if (!isset($object->$name) || ($object->$name !== $object)) {
						unset($object->$name);
						// Here SM also unset the property with id_ prefix
						// TODO SM thinks we would better update the AOP compiler to add this unset in __unset()
						$id_name = 'id_' . $name;
						if (isset($object->$id_name)) {
							unset($object->$id_name);
						}
					}
				}
				else {
					$property->setValue($object, null);
				}
			}
		}
		if ($deactivate_aop) {
			AOP::propertiesOff($object);
		}
		return $object;
	}

}
