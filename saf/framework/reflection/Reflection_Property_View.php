<?php
namespace SAF\Framework\Reflection;

use DateTime;
use SAF\Framework\Tools\Date_Time;
use SAF\Framework\Tools\Password;

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

	//----------------------------------------------------------------------------- getFormattedValue
	/**
	 * Format the property value, taken from the input object, depending on it's type
	 *
	 * @param $object object
	 * @return string
	 */
	public function getFormattedValue($object)
	{
		return $this->formatValue($this->property->getValue($object));
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

	//--------------------------------------------------------------------------------- formatBoolean
	/**
	 * Return 'yes' or 'no' depending on the value of the boolean
	 * If the property has a @values annotation : the first value is for 'no', the second for 'yes'
	 *
	 * @param $value
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
			: ($value ? 'yes' : 'no');
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
		return (is_null($value) && $this->property->getAnnotation('null')->value) ? null : ($value + 0);
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
		return (is_null($value) && $this->property->getAnnotation('null')->value) ? null : ($value + 0);
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
		if ($this->property->getAnnotation('password')->value) {
			$value = strlen($value) ? str_repeat('*', strlen(Password::UNCHANGED)) : '';
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
				case Type::BOOLEAN: return $this->formatBoolean($value);
				case Type::FLOAT:   return $this->formatFloat($value);
				case Type::INTEGER: return $this->formatInteger($value);
				case Type::STRING:  return $this->formatString($value);
			}
			return $this->formatDefault($value);
		}
	}

}
