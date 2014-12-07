<?php
namespace SAF\Framework;

use SAF\Framework\Locale\Date_Format;
use SAF\Framework\Locale\Number_Format;
use SAF\Framework\Locale\Translations;
use SAF\Framework\Plugin\Configurable;
use SAF\Framework\Reflection\Interfaces\Reflection_Method;
use SAF\Framework\Reflection\Interfaces\Reflection_Property;
use SAF\Framework\Reflection\Reflection_Property_Value;
use SAF\Framework\Reflection\Type;
use SAF\Framework\Tools\Current;

/**
 * A Locale object has all locale features, useful for specific locale conversions
 */
class Locale implements Configurable
{
	use Current { current as private pCurrent; }

	//----------------------------------------------------- Locale configuration array keys constants
	const DATE     = 'date';
	const LANGUAGE = 'language';
	const NUMBER   = 'number';

	//----------------------------------------------------------------------------------------- $date
	/**
	 * @link Object
	 * @setter setDateFormat
	 * @var Date_Format
	 */
	public $date_format;

	//------------------------------------------------------------------------------------- $language
	/**
	 * @setter setLanguage
	 * @var string
	 */
	public $language;

	//--------------------------------------------------------------------------------------- $number
	/**
	 * @setter setNumberFormat
	 * @var Number_Format
	 */
	public $number_format;

	//--------------------------------------------------------------------------------- $translations
	/**
	 * @var Translations
	 */
	public $translations;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $configuration array
	 */
	public function __construct($configuration)
	{
		$current = self::current();
		if (!isset($current)) {
			self::current($this);
		}
		$this->setDateFormat($configuration[self::DATE]);
		$this->setLanguage($configuration[self::LANGUAGE]);
		$this->setNumberFormat($configuration[self::NUMBER]);
	}

	//--------------------------------------------------------------------------------------- current
	/**
	 * @param $set_current Locale
	 * @return Locale
	 */
	public static function current(Locale $set_current = null)
	{
		return self::pCurrent($set_current);
	}

	//-------------------------------------------------------------------------------- methodToLocale
	/**
	 * Change an ISO value into a locale formatted value, knowing it's method
	 *
	 * @param $method Reflection_Method
	 * @param $value  string
	 * @return string
	 */
	public function methodToLocale(Reflection_Method $method, $value)
	{
		return $this->toLocale($value, new Type($method->returns()));
	}

	//--------------------------------------------------------------------------------- propertyToIso
	/**
	 * Change a locale value into an ISO formatted value, knowing it's property
	 *
	 * @param $property Reflection_Property
	 * @param $value    string
	 * @return string|integer|float
	 */
	public function propertyToIso(Reflection_Property $property, $value = null)
	{
		if (($property instanceof Reflection_Property_Value) && !isset($value)) {
			$value = $property->value();
		}
		return $this->toIso($value, $property->getType());
	}

	//------------------------------------------------------------------------------ propertyToLocale
	/**
	 * Change an ISO value into a locale formatted value, knowing it's property
	 *
	 * @param $property Reflection_Property
	 * @param $value    string
	 * @return string
	 */
	public function propertyToLocale(Reflection_Property $property, $value = null)
	{
		if (($property instanceof Reflection_Property_Value) && !isset($value)) {
			$value = $property->value();
		}
		return $this->toLocale($value, $property->getType());
	}

	//--------------------------------------------------------------------------------- setDateFormat
	/**
	 * @param $date_format Date_Format|string if string, must be a date format (ie 'd/m/Y')
	 */
	private function setDateFormat($date_format)
	{
		$this->date_format = ($date_format instanceof Date_Format)
			? $date_format
			: new Date_Format($date_format);
	}

	//----------------------------------------------------------------------------------- setLanguage
	/**
	 * @param $language string
	 */
	private function setLanguage($language)
	{
		$this->language = $language;
		$this->translations = new Translations($this->language);
	}

	//------------------------------------------------------------------------------- setNumberFormat
	/**
	 * Set locale's number format
	 *
	 * @param Number_Format|mixed[]
	 */
	private function setNumberFormat($number_format)
	{
		$this->number_format = ($number_format instanceof Number_Format)
			? $number_format
			: new Number_Format($number_format);
	}

	//----------------------------------------------------------------------------------------- toIso
	/**
	 * Change a locale value into an ISO formatted value, knowing it's data type
	 *
	 * @param $value string
	 * @param $type  Type
	 * @return string|integer|float
	 */
	public function toIso($value, Type $type = null)
	{
		if (isset($type)) {
			if ($type->isDateTime()) {
				return $this->date_format->toIso($value);
			}
			elseif ($type->isFloat()) {
				return $this->number_format->floatToIso($value);
			}
			elseif ($type->isInteger()) {
				return $this->number_format->integerToIso($value);
			}
		}
		return $value;
	}

	//-------------------------------------------------------------------------------------- toLocale
	/**
	 * Change an ISO value into a locale formatted value, knowing it's data type
	 *
	 * @param $type  Type
	 * @param $value string
	 * @return string
	 */
	public function toLocale($value, Type $type = null)
	{
		if (isset($type)) {
			if ($type->isDateTime()) {
				return $this->date_format->toLocale($value);
			}
			elseif ($type->isFloat()) {
				return $this->number_format->floatToLocale($value);
			}
			elseif ($type->isInteger()) {
				return $this->number_format->integerToLocale($value);
			}
		}
		return $value;
	}

}
