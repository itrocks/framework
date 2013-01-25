<?php
namespace SAF\Framework;

trait Current_With_Default
{
	use Current { current as private pCurrent; }

	//--------------------------------------------------------------------------------------- current
	/**
	 * Gets/sets current environment's object
	 *
	 * @param $set_current Current_With_Default
	 * @return Current_With_Default
	 */
	public static function current($set_current = null)
	{
		$current = self::pCurrent($set_current);
		if (!isset($current)) {
			$class = get_called_class();
			$current = self::pCurrent(new $class());
		}
		return $current;
	}

}
