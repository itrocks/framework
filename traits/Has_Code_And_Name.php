<?php
namespace ITRocks\Framework\Traits;

use ITRocks\Framework\Dao;

/**
 * A business trait for class that need a code and a name
 *
 * @representative code, name
 */
trait Has_Code_And_Name
{
	use Has_Code;
	use Has_Name { __toString as private hasNameToString; }

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
	 * - first get all codes matching the string as static::$code
	 * - If none found, get all codes matching the string as static::$name
	 *
	 * @param $value string
	 * @return static
	 */
	public static function fromString($value)
	{
		return reset(static::fromStringMultiple($value));
	}

	//---------------------------------------------------------------------------- fromStringMultiple
	/**
	 * Returns the Codes that match a string
	 * - first get all codes matching the string as static::$code
	 * - If none found, get all codes matching the string as static::$name
	 *
	 * @param $value string
	 * @return static[]
	 */
	public static function fromStringMultiple($value)
	{
		$values = Dao::search(['code' => $value], static::class);
		if (!$values) {
			$values = Dao::search(['name' => $value], static::class);
		}
		return $values;
	}

	//---------------------------------------------------------------------------------------- sameAs
	/**
	 * Returns true if the two objects are the same, in a code?:name meaning
	 *
	 * - if at least one of them has a code and the codes are equal : it is the same
	 * - else if they have no code and the names are equal : it is the same
	 *
	 * @param $object static
	 * @return boolean
	 */
	public function sameAs($object)
	{
		return ($object->code || $this->code)
			? ($object->code === $this->code)
			: ($object->name === $this->name);
	}

}
