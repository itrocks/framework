<?php
namespace SAF\Framework\Objects;

use SAF\Framework\Dao;
use SAF\Framework\Traits\Has_Name;

/**
 * Standard basic codes, with a code and a full name
 *
 * @representative code, name
 */
class Code
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
	 * @param $value
	 * @return static[]
	 */
	public static function fromString($value)
	{
		$values = Dao::search(['code' => $value], static::class);
		if (!$values) {
			$values = Dao::search(['name' => $value], static::class);
		}
		return $values;
	}

}
