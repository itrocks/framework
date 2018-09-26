<?php
namespace ITRocks\Framework\Address;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Traits\Has_Code_And_Name;

/**
 * A physical person civility
 *
 * @feature
 */
class Civility
{
	use Has_Code_And_Name;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->code);
	}

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

}
