<?php
namespace SAF\Framework\Address;

use SAF\Framework\Dao;
use SAF\Framework\Objects\Code;

/**
 * A physical person civility
 *
 * @feature
 */
class Civility extends Code
{

	//---------------------------------------------------------------------------------------- getAll
	/**
	 * @return static[]
	 */
	public static function getAll()
	{
		/** @var $civilities static[] */
		$civilities = Dao::readAll(get_called_class());
		return $civilities;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->code);
	}

}
