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
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name     class-string<T>
	 * @param $deactivate_aop boolean true to disable AOP on properties for the search object
	 * @return T
	 * @template T
	 */
	public static function create(string $class_name, bool $deactivate_aop = false) : object
	{
		/** @noinspection PhpUnhandledExceptionInspection $class_name must be valid */
		$object = Builder::create($class_name);
		/** @noinspection PhpUnhandledExceptionInspection object */
		foreach ((new Reflection_Class($object))->getProperties() as $property) {
			if ($property->isPublic() && !$property->isStatic()) {
				$name = $property->name;
				unset($object->$name);
				unset($object->{"id_$name"});
			}
		}
		if ($deactivate_aop) {
			AOP::propertiesOff($object);
		}
		$object->_search_object = true;
		return $object;
	}

	//-------------------------------------------------------------------------------------------- is
	/**
	 * Returns true if the object was created using Search_Object::create()
	 *
	 * @param $object object
	 * @return boolean
	 */
	public static function is(object $object) : bool
	{
		return isset($object->_search_object) && $object->_search_object;
	}

}