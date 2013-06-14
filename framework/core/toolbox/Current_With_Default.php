<?php
namespace SAF\Framework;

/** @noinspection PhpIncludeInspection */
require_once "framework/core/toolbox/Current.php";

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
	 * @param $set_current object
	 * @return object
	 */
	public static function current($set_current = null)
	{
		$current = self::pCurrent($set_current);
		if (!isset($current)) {
			$class = get_called_class();
			$current = self::pCurrent(new $class);
		}
		return $current;
	}

}
