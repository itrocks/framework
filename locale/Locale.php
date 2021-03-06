<?php
namespace ITRocks\Framework;

use ITRocks\Framework\Feature\Validate\Property\Mandatory_Annotation;
use ITRocks\Framework\Locale\Date_Format;
use ITRocks\Framework\Locale\Number_Format;
use ITRocks\Framework\Locale\Translation;
use ITRocks\Framework\Locale\Translator;
use ITRocks\Framework\Plugin\Configurable;
use ITRocks\Framework\Plugin\Has_Get;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Reflection\Annotation\Property\Encrypt_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Null_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Password_Annotation;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Method;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;
use ITRocks\Framework\Reflection\Reflection_Property_Value;
use ITRocks\Framework\Reflection\Type;
use ITRocks\Framework\Tools\Current;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Tools\Password;
use ITRocks\Framework\Updater\Application_Updater;
use ITRocks\Framework\Updater\Updatable;

/**
 * A Locale object has all locale features, useful for specific locale conversions
 */
class Locale implements Configurable, Registerable, Updatable
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

	//----------------------------------------------------------------------------- $format_translate
	/**
	 * @var boolean
	 */
	public $format_translate = true;

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
	public function __construct($configuration = null)
	{
		$current = self::current();
		if (!isset($current)) {
			self::current($this);
		}
		if (!$configuration) {
			return;
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
	 * @return string|integer|float|Date_Time
	 */
	public function propertyToIso(Reflection_Property $property, $value = null)
	{
		if (($property instanceof Reflection_Property_Value) && !isset($value)) {
			$value = $property->value();
		}
		$type = $property->getType();
		if (
			!$value
			&& $type->isDateTime()
			&& ($property->getAnnotation('default')->value === (Date_Time::class . '::max'))
		) {
			return Date_Time::max();
		}
		return (is_null($value) && Null_Annotation::of($property)->value)
			? $value
			: $this->toIso($value, $type);
	}

	//------------------------------------------------------------------------------ propertyToLocale
	/**
	 * Change an ISO value into a locale formatted value, knowing its property
	 *
	 * @param $property Reflection_Property
	 * @param $value    string
	 * @return mixed
	 */
	public function propertyToLocale(Reflection_Property $property, $value = null) : mixed
	{
		$called_user_getter = false;
		if ($property instanceof Reflection_Property_Value) {
			if (!isset($value)) {
				$value = $property->value();
			}
			if ($property->user && $property->getAnnotation('user_getter')->value) {
				$called_user_getter = true;
			}
		}
		$type = $property->getUserType();
		if (is_null($value) && Null_Annotation::of($property)->value) {
			return null;
		}
		if (is_null($value) && $type->isNumeric() && Mandatory_Annotation::of($property)->value) {
			$value = 0;
		}
		if (Encrypt_Annotation::of($property)->value ?: Password_Annotation::of($property)->value) {
			$value = strlen($value) ? str_repeat('*', strlen(Password::UNCHANGED)) : '';
		}
		elseif ($type->isDateTime() && (($value instanceof Date_Time) || !$called_user_getter)) {
			$this->date_format->show_seconds = $property->getAnnotation('show_seconds')->value;
			$this->date_format->show_time    = $property->getAnnotation('show_time')->value;
			// force call of toLocale(), needed for date-times
			$called_user_getter = false;
		}
		elseif ($type->isFloat() && (isStrictNumeric($value) || !$called_user_getter)) {
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
		if (is_null($value) && Null_Annotation::of($property)->value) {
			$result = $value;
		}
		elseif (
			$this->format_translate
			&& (
				($values = $property->getListAnnotation('values')->value)
				|| ($property->getAnnotation('translate')->value === 'common')
			)
		) {
			if ($values && (count($values) === 2) && $type->isBoolean()) {
				$value = $value ? $values[0] : $values[1];
			}
			$result = $this->translations->translate(
				$value, $type->isClass() ? $type->getElementTypeAsString() : $property->final_class
			);
		}
		elseif (in_array($property->getAnnotation('translate')->value, ['', 'data'], true)) {
			$result = (new Translation\Data\Set)->translate($property, $value);
		}
		elseif (!$called_user_getter) {
			$result = $this->toLocale($value, $type);
		}
		else {
			$result = $value;
		}
		if (isset($save_decimals)) {
			list(
				$this->number_format->decimal_minimal_count,
				$this->number_format->decimal_maximal_count
			) = $save_decimals;
		}
		return $result;
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		Application_Updater::get()->addUpdatable($this);
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
	 * @todo When hard typing will be enabled on all properties, simplify numeric tests (no string)
	 */
	public function toLocale($value, Type $type = null)
	{
		if (isset($type)) {
			if ($type->isBoolean()) {
				if (is_null($value)) {
					return $value;
				}
				return $value ? $this->translations->translate(YES) : $this->translations->translate(NO);
			}
			if ($type->isDateTime()) {
				return $this->date_format->toLocale($value);
			}
			elseif ($type->isFloat()) {
				if (!isStrictNumeric($value)) {
					if (in_array($value, ['', null], true)) {
						return '';
					}
					else {
						trigger_error('Not a float ' . $value, E_USER_ERROR);
					}
				}
				return $this->number_format->floatToLocale(floatval($value));
			}
			elseif ($type->isInteger()) {
				if (!isStrictNumeric($value, false)) {
					if (in_array($value, ['', null], true)) {
						return '';
					}
					else {
						trigger_error('Not an integer ' . $value, E_USER_ERROR);
					}

				}
				return $this->number_format->integerToLocale(intval($value));
			}
		}
		return $value;
	}

	//---------------------------------------------------------------------------------------- update
	/**
	 * @param $last_time integer
	 */
	public function update($last_time)
	{
		// too slow to be executed on development environment
		// TODO bring it back when there will not be bad translation entries anymore
		if (Session::current()->environment === Configuration\Environment::DEVELOPMENT) {
			return;
		}
		$this->translations->deleteEmpty();
	}

}
