<?php
namespace SAF\Framework;

/**
 * A collection is an array of objects that are a component of the container object
 *
 * This means that each object of a collection should not exist without it's container object
 */
class Collection
{

	//------------------------------------------------------------------------------------------- add
	/**
	 * Add an object into an objects array
	 *
	 * @param $array   array
	 * @param $element object|object[]
	 */
	public static function add(&$array, $element)
	{
		if (is_array($element)) {
			foreach ($element as $elem) {
				self::add($array, $elem);
			}
		}
		else {
			$array[Dao::getObjectIdentifier($element)] = $element;
		}
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
	 * @param $element object|object[]
	 */
	public static function remove(&$array, $element)
	{
		if (is_array($element)) {
			foreach ($element as $elem) {
				self::remove($array, $elem);
			}
		}
		else {
			$key = Dao::getObjectIdentifier($element);
			if (!array_key_exists($key, $array)) {
				$array[$key] = $element;
			}
		}
	}

}
