<?php
namespace SAF\Framework;

use SAF\Framework\Locale\Date;
use SAF\Framework\Locale\Number;
use SAF\Framework\Locale\Translations;
use SAF\Framework\Plugin\Configurable;
use SAF\Framework\Reflection\Reflection_Property;
use SAF\Framework\Reflection\Reflection_Property_Value;
use SAF\Framework\Reflection\Type;
use SAF\Framework\Tools\Current;

/**
 * A Locale object has all locale features, useful for specific locale conversions
 */
class Locale implements Configurable
{
	use Current { current as private pCurrent; }

	//----------------------------------------------------------------------------------------- $date
	/**
	 * @link Object
	 * @setter setDate
	 * @var Date
	 */
	public $date;

	//------------------------------------------------------------------------------------- $language
	/**
	 * @setter setLanguage
	 * @var string
	 */
	public $language;

	//--------------------------------------------------------------------------------------- $number
	/**
	 * @setter setNumber
	 * @var Number
	 */
	public $number;

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
		$this->setDate($configuration['date']);
		$this->setLanguage($configuration['language']);
		$this->setNumber($configuration['number']);
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

	//--------------------------------------------------------------------------------------- setDate
	/**
	 * @param $date Date | string if string, must be a date format (ie 'd/m/Y')
	 */
	/* @noinspection PhpUnusedPrivateMethodInspection @setter */
	private function setDate($date)
	{
		$this->date = ($date instanceof Date) ? $date : new Date($date);
	}

	//----------------------------------------------------------------------------------- setLanguage
	/**
	 * @param $language string
	 */
	/* @noinspection PhpUnusedPrivateMethodInspection @setter */
	private function setLanguage($language)
	{
		$this->language = $language;
		$this->translations = new Translations($this->language);
	}

	//------------------------------------------------------------------------------------- setNumber
	/**
	 * Set locale's number
	 *
	 * @param Number | mixed[]
	 */
	/* @noinspection PhpUnusedPrivateMethodInspection @setter */
	private function setNumber($number)
	{
		$this->number = ($number instanceof Number)
			? $number
			: new Number($number);
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
				return $this->date->toIso($value);
			}
			elseif ($type->isFloat()) {
				return $this->number->floatToIso($value);
			}
			elseif ($type->isInteger()) {
				return $this->number->integerToIso($value);
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
				return $this->date->toLocale($value);
			}
			elseif ($type->isFloat()) {
				return $this->number->floatToLocale($value);
			}
			elseif ($type->isInteger()) {
				return $this->number->integerToLocale($value);
			}
		}
		return $value;
	}

}
