<?php
namespace SAF\Framework;

trait Current
{

	//--------------------------------------------------------------------------------------- current
	/**
	 * Gets/sets current environment's object
	 *
	 * @param object $set_current
	 */
	public static function current($set_current = null)
	{
		static $current = null;
		if ($set_current) {
			$current = $set_current;
		}
		return $current;
	}

}
