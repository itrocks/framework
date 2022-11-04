<?php
namespace ITRocks\Framework\Tools;

/**
 * Use this trait into classes to have a default current value of an object into the current context
 *
 * @see Current
 */
trait Current_With_Default
{
	use Current { current as private pCurrent; }

	//--------------------------------------------------------------------------------------- current
	/**
	 * Gets/sets current environment's object
	 *
	 * @param $set_current object|null
	 * @return ?object
	 */
	public static function current(object $set_current = null) : ?object
	{
		$current = self::pCurrent($set_current);
		if (!isset($current)) {
			$current = self::pCurrent(new static);
		}
		return $current;
	}

}
