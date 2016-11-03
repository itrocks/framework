<?php
namespace ITRocks\Framework\Address;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Objects\Code;

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
