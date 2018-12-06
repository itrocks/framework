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
	 * @param $set_current static
	 * @return static|null
	 */
	public static function current($set_current = null)
	{
		$current = self::pCurrent($set_current);
		if (!isset($current)) {
			$current = self::pCurrent(new static);
		}
		return $current;
	}

}
