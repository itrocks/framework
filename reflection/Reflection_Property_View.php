<?php
namespace ITRocks\Framework\Reflection;

use ITRocks\Framework\Locale\Loc;

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

	//----------------------------------------------------------------------------------- formatValue
	/**
	 * @param $value mixed
	 * @return string
	 */
	public function formatValue($value)
	{
		$type = $this->property->getType();
		if (is_string($value) && $type->isString()) {
			$value = str_replace(['<', '>'], ['&lt;', '&gt;'], $value);
			$value = preg_replace('/{(~\s)/', '&lbrace;$1', $value);
			$value = preg_replace('/(~\s)}/', '$1&rbrace;', $value);
		}
		elseif (is_array($value) && $type->isMultipleString()) {
			foreach ($value as &$val) {
				$val = str_replace(['<', '>'], ['&lt;', '&gt;'], $val);
				$val = preg_replace('/{(~\s)/', '&lbrace;$1', $val);
				$val = preg_replace('/(~\s)}/', '$1&rbrace;', $val);
			}
		}
		return $type->isBasic()
			? Loc::propertyToLocale($this->property, $value)
			: $value;
	}

	//----------------------------------------------------------------------------- getFormattedValue
	/**
	 * Format the property value, taken from the input object, depending on it's type
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object      object|mixed
	 * @param $final_value boolean
	 * @return string
	 */
	public function getFormattedValue($object, $final_value = false)
	{
		/** @noinspection PhpUnhandledExceptionInspection $property belongs to $object class */
		return $this->formatValue($final_value ? $object : $this->property->getValue($object));
	}

}
