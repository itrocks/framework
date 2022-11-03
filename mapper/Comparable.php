<?php
namespace ITRocks\Framework\Mapper;

/**
 * Contract of comparable objects classes
 */
interface Comparable
{

	//--------------------------------------------------------------------------------------- compare
	/**
	 * Compare the current object with another compatible object
	 *
	 * @param $what object
	 * @param $with object
	 * @return integer -1, 0 or 1
	 */
	public static function compare(object $what, object $with) : int;

}
