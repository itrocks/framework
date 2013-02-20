<?php
namespace SAF\Framework;
use AopJoinpoint;

abstract class Loc implements Plugin
{

	//----------------------------------------------------- afterHtmlTemplateFuncsToEditPropertyExtra
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function afterHtmlTemplateFuncsToEditPropertyExtra(AopJoinpoint $joinpoint)
	{
		/** @var $property      Reflection_Property */
		/** @var $property_path string */
		/** @var $value         mixed */
		list($property, $property_path, $value) = $joinpoint->getReturnedValue();
		$value = self::propertyToLocale($property, $value);
		$joinpoint->setReturnedValue(array($property, $property_path, $value));
	}

	//------------------------------------------------------------------------- afterListSearchValues
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function afterListSearchValues(AopJoinpoint $joinpoint)
	{
		/** @var $search Reflection_Property_Value[] */
		$search = $joinpoint->getReturnedValue();
		if (isset($search)) {
			foreach ($search as $property) {
				$property->value(self::propertyToIso($property));
			}
			$joinpoint->setReturnedValue($search);
		}
	}

	//---------------------------------------- beforeDefaultWriteControllerFormElementToPropertyValue
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function beforeDefaultWriteControllerFormElementToPropertyValue(
		AopJoinpoint $joinpoint
	) {
		/** @var $property Reflection_Property */
		list($property, $value) = $joinpoint->getArguments();
		if (isset($value)) {
			if (is_array($value) && !empty($value)) {
				if ($property->getAnnotation("link")->value == "Collection") {
					$class = Reflection_Class::getInstanceOf($property->getType()->getElementTypeAsString());
					$properties = $class->accessProperties();
					reset($value);
					if (!is_numeric(key($value))) {
						$value = arrayFormRevert($value);
					}
					foreach ($value as $key => $element) {
						foreach ($element as $property_name => $property_value) {
							if (isset($property_value) && isset($properties[$property_name])) {
								$value[$key][$property_name] = self::propertyToIso(
									$properties[$property_name], $property_value
								);
							}
						}
					}
					$class->accessPropertiesDone();
					$joinpoint->setArguments(array($property, $value));
				}
			}
			else {
				$joinpoint->setArguments(array($property, self::propertyToIso($property, $value)));
			}
		}
	}

	//------------------------------------------------------------------------------------------ date
	/**
	 * Returns current date
	 *
	 * @return Date_Locale
	 */
	public static function date()
	{
		return Locale::current()->date;
	}

	//---------------------------------------------------------------------------------- dateToLocale
	/**
	 * Takes an ISO date and make it locale
	 *
	 * @param $date string ie "2001-12-25" "2001-12-25 12:20:00" "2001-12-25 12:20:16"
	 * @return string "25/12/2011" "25/12/2001 12:20" "25/12/2001 12:20:16"
	 */
	public static function dateToLocale($date)
	{
		return Locale::current()->date->toLocale($date);
	}

	//----------------------------------------------------------------- dateTimeReturnedValueToLocale
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function dateTimeReturnedValueToLocale(AopJoinpoint $joinpoint)
	{
		$joinpoint->setReturnedValue(self::dateToLocale($joinpoint->getReturnedValue()));
	}

	//------------------------------------------------------------------------------------- dateToIso
	/**
	 * Takes a locale date and make it ISO
	 *
	 * @param $date string ie "25/12/2001" "25/12/2001 12:20" "25/12/2001 12:20:16"
	 * @return string ie "2001-12-25" "2001-12-25 12:20:00" "2001-12-25 12:20:16"
	 */
	public static function dateToIso($date)
	{
		return Locale::current()->date->toIso($date);
	}

	//-------------------------------------------------------------------- floatReturnedValueToLocale
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function floatReturnedValueToLocale(AopJoinpoint $joinpoint)
	{
		$joinpoint->setReturnedValue(self::floatToLocale($joinpoint->getReturnedValue()));
	}

	//------------------------------------------------------------------------------------ floatToIso
	/**
	 * @param $float    string
	 * @param $property Reflection_Property
	 * @return float
	 */
	public static function floatToIso($float, Reflection_Property $property = null)
	{
		return Locale::current()->number->floatToIso($float, $property);
	}

	//--------------------------------------------------------------------------------- floatToLocale
	/**
	 * Takes a float number and make it locale
	 *
	 * @param $float float ie 1000 1000.28 1000.2148
	 * @return string ie "1 000,00" "1 000,28" "1 000,2148"
	 */
	public static function floatToLocale($float)
	{
		return Locale::current()->number->floatToLocale($float);
	}

	//------------------------------------------------------------------ integerReturnedValueToLocale
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function integerReturnedValueToLocale(AopJoinpoint $joinpoint)
	{
		$joinpoint->setReturnedValue(self::integerToLocale($joinpoint->getReturnedValue()));
	}

	//---------------------------------------------------------------------------------- integerToIso
	/**
	 * @param $integer  string
	 * @param $property Reflection_Property
	 * @return integer
	 */
	public static function integerToIso($integer, Reflection_Property $property = null)
	{
		return Locale::current()->number->integerToIso($integer, $property);
	}

	//------------------------------------------------------------------------------- integerToLocale
	/**
	 * Takes an integer and make it locale
	 *
	 * @param $integer integer ie 1000
	 * @return string ie "1 000"
	 */
	public static function integerToLocale($integer)
	{
		return Locale::current()->number->integerToLocale($integer);
	}

	//-------------------------------------------------------------------------------------- language
	/**
	 * Returns current language
	 *
	 * @return string
	 */
	public static function language()
	{
		return Locale::current()->language;
	}

	//--------------------------------------------------------------------------------- propertyToIso
	/**
	 * Change a locale value into an ISO formatted value, knowing it's property
	 *
	 * @param $property Reflection_Property
	 * @param $value    string
	 * @return mixed
	 */
	public static function propertyToIso(Reflection_Property $property, $value = null)
	{
		return Locale::current()->propertyToIso($property, $value);
	}

	//------------------------------------------------------------------------------ propertyToLocale
	/**
	 * Change an ISO value into a locale formatted value, knowing it's property
	 *
	 * @param $property Reflection_Property
	 * @param $value    mixed
	 * @return string
	 */
	public static function propertyToLocale(Reflection_Property $property, $value = null)
	{
		return Locale::current()->propertyToLocale($property, $value);
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		// format from locale user input to ISO and standard formats
		Aop::add("before",
			'SAF\Framework\Default_Write_Controller->formElementToPropertyValue()',
			array(__CLASS__, "beforeDefaultWriteControllerFormElementToPropertyValue")
		);
		Aop::add("after",
			'SAF\Framework\Default_List_Controller->getSearchValues()',
			array(__CLASS__, "afterListSearchValues")
		);
		// format to locale
		Aop::add("after",
			'SAF\Framework\Html_Template_Funcs->toEditPropertyExtra()',
			array(__CLASS__, "afterHtmlTemplateFuncsToEditPropertyExtra")
		);
		Aop::add("after",
			'SAF\Framework\Reflection_Property_View->formatDateTime()',
			array(__CLASS__, "dateTimeReturnedValueToLocale")
		);
		Aop::add("after",
			'SAF\Framework\Reflection_Property_View->formatFloat()',
			array(__CLASS__, "floatReturnedValueToLocale")
		);
		Aop::add("after",
			'SAF\Framework\Reflection_Property_View->formatInteger()',
			array(__CLASS__, "integerReturnedValueToLocale")
		);
	}

	//------------------------------------------------------------------------------------------- rtr
	/**
	 * Reverse translation
	 *
	 * @param $translation string
	 * @param $context     string
	 * @return string
	 */
	public static function rtr($translation, $context = "")
	{
		/** @noinspection PhpVoidFunctionResultUsedInspection */
		return Locale::current()->translations->reverse($translation, $context);
	}

	//-------------------------------------------------------------------------------------------- tr
	/**
	 * Translation
	 *
	 * @param $text    string
	 * @param $context string
	 * @return string
	 */
	public static function tr($text, $context = "")
	{
		return Locale::current()->translations->translate($text, $context);
	}

}
