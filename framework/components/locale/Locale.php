<?php
namespace SAF\Framework;

class Locale
{
	use Current { current as private pCurrent; }

	//----------------------------------------------------------------------------------------- $date
	/**
	 * @getter Aop::getObject
	 * @setter setDate
	 * @var Date_Locale
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
	 * @var Number_Format
	 */
	public $number;

	//--------------------------------------------------------------------------------- $translations
	/**
	 * @var Translations
	 */
	public $translations;

	//----------------------------------------------------------------------------------- __construct
	public function __construct($parameters = null)
	{
		if (isset($parameters)) {
			$this->setDate($parameters["date"]);
			$this->setLanguage($parameters["language"]);
			$this->setNumber($parameters["number"]);
		}
	}

	//--------------------------------------------------------------------------------------- current
	/**
	 * @param Locale $set_current
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
	 * @param Reflection_Property $property
	 * @param string $value
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
	 * @param Reflection_Property $property
	 * @param string $value
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
	 * @param Date_Locale | string $date if string, must be a date format (ie "d/m/Y")
	 */
	public function setDate($date)
	{
		$this->date = ($date instanceof Date_Locale)
			? $date
			: new Date_Locale($date);
	}

	//----------------------------------------------------------------------------------- setLanguage
	/**
	 * @param string $language
	 */
	public function setLanguage($language)
	{
		$this->language = $language;
		$this->translations = new Translations($this->language);
	}

	//------------------------------------------------------------------------------------- setNumber
	/**
	 * Set locale's number
	 *
	 * @param Number_Locale | mixed[]
	 */
	public function setNumber($number)
	{
		$this->number = ($number instanceof Number_Locale)
			? $number
			: new Number_Locale($number);
	}

	//----------------------------------------------------------------------------------------- toIso
	/**
	 * Change a locale value into an ISO formatted value, knowing it's data type
	 *
	 * @param string $type
	 * @param string $value
	 */
	public function toIso($value, $type = null)
	{
		switch ($type) {
			case "Date_Time": return $this->date->toIso($value);
			case "float":     return $this->number->floatToIso($value);
			case "integer":   return $this->number->integerToIso($value);
		}
		return $value;
	}

	//-------------------------------------------------------------------------------------- toLocale
	/**
	 * Change an ISO value into a locale formatted value, knowing it's data type
	 *
	 * @param string $type
	 * @param string $value
	 */
	public function toLocale($value, $type = null)
	{
		switch ($type) {
			case "Date_Time": return $this->date->toLocale($value);
			case "float":     return $this->number->floatToLocale($value);
			case "integer":   return $this->number->integerToLocale($value);
		}
		return $value;
	}

}
