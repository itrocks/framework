<?php
namespace SAF\Framework;

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
	 * Default format is ISO "0000-00-00 00:00:00"
	 *
	 * @param $value mixed
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
		return $value + 0;
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
		return $value + 0;
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
		} else {
			switch ($type) {
				case "float":   return $this->formatFloat($value);
				case "integer": return $this->formatInteger($value);
				case "string":  return $this->formatString($value);
			}
			return $this->formatDefault($value);
		}
	}

}
