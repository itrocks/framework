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
