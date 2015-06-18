<?php
namespace SAF\Framework\Address;

use SAF\Framework\Dao;
use SAF\Framework\Objects\Code;

/**
 * A physical person civility
 *
 * @business
 */
class Civility extends Code
{

	//---------------------------------------------------------------------------------------- getAll
	/**
	 * @return Civility[]
	 */
	public static function getAll()
	{
		return Dao::readAll(get_called_class());
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
