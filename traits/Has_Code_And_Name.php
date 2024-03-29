<?php
namespace ITRocks\Framework\Traits;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Reflection\Attribute\Class_\Representative;

/**
 * A business trait for class that need a code and a name
 */
#[Representative('code', 'name')]
trait Has_Code_And_Name
{
	use Has_Code;
	use Has_Name { __toString as private hasNameToString; }

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return trim($this->code . SP . $this->name);
	}

	//------------------------------------------------------------------------------------ fromString
	/**
	 * Returns the Code that match a string
	 * - first get all codes matching the string as static::$code
	 * - If none found, get the code matching the string as static::$name
	 * - If none found, create a new code
	 */
	public static function fromString(string $value) : ?static
	{
		$objects = static::fromStringMultiple($value);
		if ($objects) {
			return reset($objects);
		}
		/** @noinspection PhpUnhandledExceptionInspection class */
		$object = Builder::create(static::class);
		$object->code = lParse(trim($value), SP);
		if (str_contains($value, SP)) {
			$object->name = trim(substr($value, strlen($object->code) + 1));
		}
		return $object;
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
	public static function fromStringMultiple(string $value) : array
	{
		/** @var $values static[] */
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
	 */
	public function sameAs(object $object) : bool
	{
		return (isA($object, static::class) && ($object->code || $this->code))
			? ($object->code === $this->code)
			: ($object->name === $this->name);
	}

}
