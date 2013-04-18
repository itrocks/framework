<?php
namespace SAF\Framework;

trait Current
{

	//-------------------------------------------------------------------------------------- $current
	protected static $current = null;

	//--------------------------------------------------------------------------------------- current
	/**
	 * Gets/sets current environment's object
	 *
	 * @param $set_current mixed
	 * @return Current
	 */
	public static function current($set_current = null)
	{
		if ($set_current) {
			static::$current = $set_current;
		}
		return static::$current;
	}

}
