<?php
namespace ITRocks\Framework\Mapper;

use ITRocks\Framework\Dao;

/**
 * Collections of objects search functions library
 */
abstract class Search_Objects
{

	//------------------------------------------------------------------------------------- searchOne
	/**
	 * Search the first result of several search objects list
	 * Try each search object. When one is found, then return the first result object
	 *
	 * @param $objects object[]|array[]|array
	 * @param $class_name string|null
	 * @return ?object
	 */
	public static function searchOne(array $objects, string $class_name = null) : ?object
	{
		foreach ($objects as $object) {
			$object = Dao::searchOne($object, $class_name);
			if (isset($object)) return $object;
		}
		return null;
	}

}
