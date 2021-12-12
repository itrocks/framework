<?php
namespace ITRocks\Framework\Reflection\Annotation\Property;

use ITRocks\Framework\Reflection\Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Has_Is;
use ITRocks\Framework\Reflection\Annotation\Template\Property_Context_Annotation;
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
class Store_Annotation extends Annotation implements Property_Context_Annotation
{
	use Has_Is;

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'store';

	//----------------------------------------------------------------------------------------- FALSE
	const FALSE = 'false';

	//-------------------------------------------------------------------------------------------- GZ
	const GZ = 'gz';

	//------------------------------------------------------------------------------------------- HEX
	const HEX = 'hex';

	//------------------------------------------------------------------------------------------ JSON
	const JSON = 'json';

	//------------------------------------------------------------------------------------ JSON_CLASS
	/**
	 * The internal key to store the name of the class into the json structure
	 */
	const JSON_CLASS = '__class';

	//------------------------------------------------------------------------- JSON_CLASS_DEPRECATED
	/**
	 * Keep this as long as there are stored objects using _class into databases
	 *
	 * @deprecated The internal key to store the name of the class into the json structure
	 */
	const JSON_CLASS_DEPRECATED = '_class';

	//-------------------------------------------------------------------------------- JSON_CONSTRUCT
	/**
	 * The internal key to store the parameter(s) to send to the constructor of the class
	 */
	const JSON_CONSTRUCT = '__construct';

	//---------------------------------------------------------------------------------------- STRING
	const STRING = 'string';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value    string @values gz, hex, string
	 * @param $property Reflection_Property
	 */
	public function __construct($value, Reflection_Property $property)
	{
		parent::__construct($value);
		if (empty($this->value)) {
			if ($property->isStatic()) {
				$this->value = self::FALSE;
			}
			elseif ($property->getType()->isDateTime()) {
				$this->value = self::STRING;
			}
		}
	}

	//--------------------------------------------------------------------------------------- isFalse
	/**
	 * @return boolean
	 */
	public function isFalse()
	{
		return $this->value === self::FALSE;
	}

	//------------------------------------------------------------------------------------------ isGz
	/**
	 * @return boolean
	 */
	public function isGz()
	{
		return $this->value === self::GZ;
	}

	//----------------------------------------------------------------------------------------- isHex
	/**
	 * @return boolean
	 */
	public function isHex()
	{
		return $this->value === self::HEX;
	}

	//---------------------------------------------------------------------------------------- isJson
	/**
	 * @return boolean
	 */
	public function isJson()
	{
		return $this->value === self::JSON;
	}

	//-------------------------------------------------------------------------------------- isString
	/**
	 * Returns true if @store's value allows to store a string representation of the property value
	 *
	 * @return boolean
	 */
	public function isString()
	{
		return in_array($this->value, [self::GZ, self::HEX, self::JSON, self::STRING], true);
	}

	//-------------------------------------------------------------------------- storedPropertiesOnly
	/**
	 * Returns only non-static properties which @store annotation is not false
	 *
	 * @param $properties Reflection_Property[]
	 * @return Reflection_Property[] filtered properties list
	 */
	public static function storedPropertiesOnly(array $properties)
	{
		foreach ($properties as $key => $property) {
			if ($property->isStatic() || static::of($property)->isFalse()) {
				unset($properties[$key]);
			}
		}
		return $properties;
	}

}
