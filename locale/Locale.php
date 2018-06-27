<?php
namespace ITRocks\Framework;

use ITRocks\Framework\Locale\Date_Format;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Locale\Number_Format;
use ITRocks\Framework\Locale\Translator;
use ITRocks\Framework\Plugin\Configurable;
use ITRocks\Framework\Plugin\Has_Get;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Method;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;
use ITRocks\Framework\Reflection\Reflection_Property_Value;
use ITRocks\Framework\Reflection\Type;
use ITRocks\Framework\Tools\Current;
use ITRocks\Framework\Tools\Date_Time;

/**
 * A Locale object has all locale features, useful for specific locale conversions
 */
class Locale implements Configurable
{
	use Current { current as private pCurrent; }
	use Has_Get;

	//----------------------------------------------------- Locale configuration array keys constants
	const DATE     = 'date';
	const LANGUAGE = 'language';
	const NUMBER   = 'number';

	//---------------------------------------------------------------------------------- $date_format
	/**
	 * @link Object
	 * @setter setDateFormat
	 * @var Date_Format
	 */
	public $date_format;

	//------------------------------------------------------------------------------------- $language
	/**
	 * @impacts translations
	 * @setter setLanguage
	 * @var string
	 */
	public $language;

	//-------------------------------------------------------------------------------- $number_format
	/**
	 * @setter setNumberFormat
	 * @var Number_Format
	 */
	public $number_format;

	//--------------------------------------------------------------------------------- $translations
	/**
	 * @var Translator
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
	 * Change an ISO value into a locale formatted value, knowing its method
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
	 * Change a locale value into an ISO formatted value, knowing its property
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
		return (is_null($value) && $property->getAnnotation('null')->value)
			? $value
			: $this->toIso($value, $property->getType());
	}

	//------------------------------------------------------------------------------ propertyToLocale
	/**
	 * Change an ISO value into a locale formatted value, knowing its property
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
		if ($value instanceof Date_Time) {
			$this->date_format->show_seconds = $property->getAnnotation('show_seconds')->value;
		}
		if ($property->getType()->isFloat()) {
			$decimals = explode(
				',',
				str_replace([CR, LF, SP, TAB], '', $property->getAnnotation('decimals')->value)
			);
			if (!isset($decimals[1])) {
				$decimals[1] = $decimals[0];
			}
			if (strlen($decimals[0])) {
				$save_decimals = [
					$this->number_format->decimal_minimal_count,
					$this->number_format->decimal_maximal_count
				];
				list(
					$this->number_format->decimal_minimal_count,
					$this->number_format->decimal_maximal_count
				) = $decimals;
			}
		}
		$result = (is_null($value) && $property->getAnnotation('null')->value)
			? $value
			: (
				$property->getListAnnotation('values')->value
					? $this->translations->translate($value)
					: $this->toLocale($value, $property->getType())
			);
		if (isset($save_decimals)) {
			list(
				$this->number_format->decimal_minimal_count,
				$this->number_format->decimal_maximal_count
			) = $save_decimals;
		}
		return $result;
	}

	//--------------------------------------------------------------------------------- setDateFormat
	/**
	 * @param $date_format Date_Format|string if string, must be a date format (ie 'd/m/Y')
	 */
	public function setDateFormat($date_format)
	{
		$this->date_format = ($date_format instanceof Date_Format)
			? $date_format
			: new Date_Format($date_format);
	}

	//----------------------------------------------------------------------------------- setLanguage
	/**
	 * @param $language string
	 */
	public function setLanguage($language)
	{
		$this->language     = $language;
		$this->translations = new Translator($this->language);
	}

	//------------------------------------------------------------------------------- setNumberFormat
	/**
	 * Set locale's number format
	 *
	 * @param $number_format Number_Format|array
	 */
	public function setNumberFormat($number_format)
	{
		$this->number_format = ($number_format instanceof Number_Format)
			? $number_format
			: new Number_Format($number_format);
	}

	//----------------------------------------------------------------------------------------- toIso
	/**
	 * Change a locale value into an ISO formatted value, knowing its data type
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
	 * Change an ISO value into a locale formatted value, knowing its data type
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
		$context = $type->isClass() ? $type->getElementTypeAsString() : Loc::getContext();
		return $this->translations->translate($value, $context);
	}

}
