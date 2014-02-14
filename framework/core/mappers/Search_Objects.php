<?php
namespace SAF\Framework;

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
	 * @param $objects object[]|array[]
	 * @param $class_name string
	 * @return object
	 */
	public static function searchOne($objects, $class_name = null)
	{
		foreach ($objects as $object) {
			$object = Dao::searchOne($object, $class_name);
			if (isset($object)) return $object;
		}
		return null;
	}

}
