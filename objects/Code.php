<?php
namespace ITRocks\Framework\Objects;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Traits\Has_Name;

/**
 * Standard basic codes, with a code and a full name
 *
 * @business
 * @representative code, name
 */
abstract class Code
{
	use Has_Name;

	//----------------------------------------------------------------------------------------- $code
	/**
	 * @var string
	 */
	public $code;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return trim($this->code . SP . $this->name);
	}

	//---------------------------------------------------------------------------------------- equals
	/**
	 * Returns true if the two codes are equal :
	 * - if at least one of them has a code and the codes are equal : it is the same
	 * - else if they have no code and the names are equal : it is the same
	 *
	 * @param $code Code
	 * @return boolean
	 */
	public function equals(Code $code)
	{
		return ($this->code || $code->code)
			? ($this->code === $code->code)
			: ($this->name === $code->name);
	}

	//------------------------------------------------------------------------------------ fromString
	/**
	 * Returns the Codes that match a string
	 * - first get all codes matching the string as Code::$code
	 * - If none found, get all codes matching the string as Code::$name
	 *
	 * @param $value string
	 * @return static[]
	 */
	public static function fromString($value)
	{
		/** @var $values static[] */
		$values = Dao::search(['code' => $value], static::class);
		if (!$values) {
			$values = Dao::search(['name' => $value], static::class);
		}
		return $values;
	}

}
