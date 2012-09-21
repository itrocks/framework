<?php
namespace SAF\Framework;

trait Current_With_Default
{
	use Current;

	//--------------------------------------------------------------------------------------- current
	/**
	 * Gets/sets current environment's object
	 *
	 * @param Current_With_Default $set_current
	 */
		public static function current($set_current = null)
	{
		$current = parent::current($set_current);
		if (!isset($current)) {
			$class = get_called_class();
			$current = parent::current(new $class());
		}
		return $current;
	}

}
