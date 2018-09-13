<?php
namespace ITRocks\Framework\Reflection;

use DateTime;
use ITRocks\Framework\Reflection\Annotation\Property\Encrypt_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Null_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Password_Annotation;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Tools\Password;

/**
 * This is a way to display a property value into a view
 *
 * This is an entry point for localization plugins as Locale that need to format data being viewed.
 */
class Reflection_Property_View
{

	//------------------------------------------------------------------------------------- $property
	/**
	 * @var Reflection_Property
	 */
	public $property;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $property Reflection_Property
	 */
	public function __construct(Reflection_Property $property = null)
	{
		if (isset($property)) {
			$this->property = $property;
		}
	}

	//--------------------------------------------------------------------------------- formatBoolean
	/**
	 * Return 'yes' or 'no' depending on the value of the boolean
	 * If the property has a @values annotation : the first value is for 'no', the second for 'yes'
	 *
	 * @param $value boolean|null
	 * @return string
	 */
	protected function formatBoolean($value)
	{
		$values = $this->property->getListAnnotation('values')->values();
		if (count($values) == 2) {
			return $value ? $values[0] : $values[1];
		}
		return (is_null($value) && $this->property->getAnnotation('null')->value)
			? null
			: ($value ? YES : NO);
	}

	//-------------------------------------------------------------------------------- formatDateTime
	/**
	 * Returns the value with datetime format
	 *
	 * Default format is ISO '0000-00-00 00:00:00'
	 *
	 * @param $value string|DateTime|Date_Time
	 * @return mixed
	 */
	protected function formatDateTime($value)
	{
		return strval($value);
	}

	//--------------------------------------------------------------------------------- formatDefault
	/**
	 * Returns the value itself
	 *
	 * @param $value mixed
	 * @return mixed
	 */
	protected function formatDefault($value)
	{
		return $value;
	}

	//----------------------------------------------------------------------------------- formatFloat
	/**
	 * Returns the value with float format
	 *
	 * @param $value float
	 * @return string
	 */
	protected function formatFloat($value)
	{
		$null = $this->property->getAnnotation(Null_Annotation::NULL);
		return (is_null($value) && $null->value) ? null : floatval($value);
	}

	//--------------------------------------------------------------------------------- formatInteger
	/**
	 * Returns the value with integer format
	 *
	 * @param $value integer
	 * @return string
	 */
	protected function formatInteger($value)
	{
		return (is_null($value) && $this->property->getAnnotation('null')->value)
			? null
			: intval($value);
	}

	//---------------------------------------------------------------------------------- formatString
	/**
	 * Returns the value with string format
	 *
	 * @param $value string
	 * @return string
	 */
	protected function formatString($value)
	{
		if (
			Encrypt_Annotation::of($this->property)->value
			?: Password_Annotation::of($this->property)->value
		) {
			$value = strlen($value) ? str_repeat('*', strlen(Password::UNCHANGED)) : '';
		}
		elseif ($this->property->getAnnotation('values')->value) {
			$value = Names::propertyToDisplay($value);
		}
		return str_replace(['{', '}'], ['&lbrace;', '&rbrace;'], $value);
	}

	//----------------------------------------------------------------------------- formatStringArray
	/**
	 * Return translated value with string or array format
	 *
	 * @param $value string|string[]
	 * @return string
	 */
	public function formatStringArray($value)
	{
		if ($value && $this->property->getAnnotation('values')->value) {
			if (!is_array($value)) {
				$value = explode(',', $value);
			}
			foreach ($value as $key => $val) {
				$value[$key] = $this->formatString($val);
			}
			$value = join(', ', $value);
		}
		return $value;
	}

	//----------------------------------------------------------------------------------- formatValue
	/**
	 * @param $value mixed
	 * @return string
	 */
	public function formatValue($value)
	{
		$type = $this->property->getType();
		if ($type->isDateTime()) {
			return $this->formatDateTime($value);
		}
		else {
			switch ($type) {
				case Type::BOOLEAN:       return $this->formatBoolean($value);
				case Type::FLOAT:         return $this->formatFloat($value);
				case Type::INTEGER:       return $this->formatInteger($value);
				case Type::STRING:        return $this->formatString($value);
				case Type::STRING_ARRAY:  return $this->formatStringArray($value);
			}
			return $this->formatDefault($value);
		}
	}

	//----------------------------------------------------------------------------- getFormattedValue
	/**
	 * Format the property value, taken from the input object, depending on it's type
	 *
	 * @param $object      object|mixed
	 * @param $final_value boolean
	 * @return string
	 */
	public function getFormattedValue($object, $final_value = false)
	{
		return $this->formatValue($final_value ? $object : $this->property->getValue($object));
	}

}
