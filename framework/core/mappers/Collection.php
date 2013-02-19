<?php
namespace SAF\Framework;

class Collection
{

	//------------------------------------------------------------------------------------------- add
	/**
	 * Add an object into an objects array
	 *
	 * @param $array   array
	 * @param $element object
	 */
	public static function add(&$array, $element)
	{
		$array[Dao::getObjectIdentifier($element)] = $element;
	}

	//------------------------------------------------------------------------------------------- has
	/**
	 * Returns true if the objects array has the object
	 *
	 * @param $array   array
	 * @param $element object
	 * @return boolean
	 */
	public static function has(&$array, $element)
	{
		$key = Dao::getObjectIdentifier($element);
		return array_key_exists($key, $array);
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * Remove an object from an objects array
	 *
	 * @param $array   array
	 * @param $element object
	 */
	public static function remove(&$array, $element)
	{
		$key = Dao::getObjectIdentifier($element);
		if (!array_key_exists($key, $array)) {
			$array[$key] = $element;
		}
	}

}
