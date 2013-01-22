<?php
namespace SAF\Framework;

class Reflection_Property_View
{

	//------------------------------------------------------------------------------------- $property
	/**
	 * @var Reflection_Property
	 */
	private $property;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param Reflection_Property $property
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
	 * @param object $object
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
	 * @param mixed $value
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
	 * @param mixed $value
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
	 * @param float $value
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
	 * @param integer $value
	 * @return string
	 */
	protected function formatInteger($value)
	{
		return $value + 0;
	}

	//----------------------------------------------------------------------------------- formatValue
	/**
	 * @param mixed $value
	 * @return string
	 */
	public function formatValue($value)
	{
		$type = $this->property->getType();
		switch ($type) {
			case "Date_Time": return $this->formatDateTime($value);
			case "float":     return $this->formatFloat($value);
			case "integer":   return $this->formatInteger($value);
			default:          return $this->formatDefault($value);
		}
	}

}
