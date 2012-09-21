<?php
namespace SAF\Framework;

trait Current
{

	//-------------------------------------------------------------------------------------- $current
	private static $current = null;

	//--------------------------------------------------------------------------------------- current
	/**
	 * Gets/sets current environment's object
	 *
	 * @param Current $set_current
	 */
	public static function current($set_current = null)
	{
		if ($set_current) {
			self::$current = $set_current;
		}
		return self::$current;
	}

}
