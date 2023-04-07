<?php
namespace ITRocks\Framework\Reflection\Attribute\Property;

use Attribute;
use ITRocks\Framework\Reflection\Attribute\Always;
use ITRocks\Framework\Reflection\Attribute\Common;
use ITRocks\Framework\Reflection\Attribute\Inheritable;
use ITRocks\Framework\Reflection\Attribute\Template\Has_Set_Final;
use ITRocks\Framework\Reflection\Interfaces\Reflection;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;

/**
 * Sets the storage format of the property into the data set
 * - string to store any object as a string.
 * > Uses __toString() and fromString() if Stringable
 * > Stores serialized object if not Stringable (__serialize() and __unserialize())
 * - hex to use hexadecimal storage functions : same as string, but tells the Dao to store using
 * hexadecimal access.
 *
 * Default values :
 * - string if the property type is Date_Time
 * - no value on all others cases
 */
#[Always, Attribute(Attribute::TARGET_PROPERTY), Inheritable]
class Store implements Has_Set_Final
{
	use Common;

	//-------------------------------------------------------------------- Store value flag constants
	public const FALSE  = 0;
	public const GZ     = 8;
	public const HEX    = 16;
	public const JSON   = 4;
	public const STORE  = 1;
	public const STRING = 2;

	//------------------------------------------------------------------------------------ JSON_CLASS
	/**
	 * The internal key to store the name of the class into the json structure
	 */
	const JSON_CLASS = '__class';

	//-------------------------------------------------------------------------------- JSON_CONSTRUCT
	/**
	 * The internal key to store the parameter(s) to send to the constructor of the class
	 */
	const JSON_CONSTRUCT = '__construct';

	//---------------------------------------------------------------------------------------- $value
	public int $value;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value bool|integer @map static::const<int>
	 */
	public function __construct(bool|int|null $value = null)
	{
		if (isset($value)) {
			$this->value = intval($value);
		}
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return strval($this->value);
	}

	//-------------------------------------------------------------------------------------------- is
	public function is(int $flags) : bool
	{
		return $this->value & $flags;
	}

	//--------------------------------------------------------------------------------------- isFalse
	/**
	 * @return boolean
	 */
	public function isFalse() : bool
	{
		return !$this->value;
	}

	//------------------------------------------------------------------------------------------ isGz
	public function isGz() : bool
	{
		return $this->value & self::GZ;
	}

	//----------------------------------------------------------------------------------------- isHex
	public function isHex() : bool
	{
		return $this->value & self::HEX;
	}

	//---------------------------------------------------------------------------------------- isJson
	public function isJson() : bool
	{
		return $this->value & self::JSON;
	}

	//-------------------------------------------------------------------------------------- isString
	/**
	 * Returns true if #Store value allows to store a string representation of the property value
	 */
	public function isString() : bool
	{
		return $this->value & (self::GZ | self::HEX | self::JSON | self::STRING);
	}

	//-------------------------------------------------------------------------------------- setFinal
	public function setFinal(Reflection|Reflection_Property $reflection) : void
	{
		if (isset($this->value)) {
			return;
		}
		if ($reflection->isStatic()) {
			$this->value = self::FALSE;
		}
		elseif ($reflection->getType()->isDateTime()) {
			$this->value = self::STRING;
		}
		else {
			$this->value = self::STORE;
		}
	}

	//-------------------------------------------------------------------------- storedPropertiesOnly
	/**
	 * Returns only non-static properties which #Store is not false
	 *
	 * @param $properties Reflection_Property[]
	 * @return Reflection_Property[] filtered properties list
	 */
	public static function storedPropertiesOnly(array $properties) : array
	{
		foreach ($properties as $key => $property) {
			if ($property->isStatic() || !static::of($property)->value) {
				unset($properties[$key]);
			}
		}
		return $properties;
	}

}
