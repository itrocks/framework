<?php
namespace SAF\Framework;

class Locale
{

	//--------------------------------------------------------------------------------------- current
	public function current($set_current = null)
	{
		static $current = null;
		if ($set_current) {
			$current = $set_current;
		} elseif (!isset($current)) {
			$current = new Locale();
		}
		return $current;
	}

}
