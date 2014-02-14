<?php
namespace SAF\Framework;

use SAF\Plugins;

/**
 * Locale plugin concentrates locale translation / formatting features into simple static calls
 */
class Loc implements Plugins\Registerable
{

	//-------------------------------------------------------------------------------------- $context
	/**
	 * Current context for translations
	 *
	 * @var string
	 */
	private static $context;

	//----------------------------------------------------- afterHtmlTemplateFuncsToEditPropertyExtra
	/**
	 * @param $result array[]
	 * @return array[]
	 */
	public function afterHtmlTemplateFuncsToEditPropertyExtra($result)
	{
		/** @var $property      Reflection_Property */
		/** @var $property_path string */
		/** @var $value         mixed */
		list($property, $property_path, $value) = $result;
		$value = self::propertyToLocale($property, $value);
		return array($property, $property_path, $value);
	}

	//------------------------------------------------------------------------- afterListSearchValues
	/**
	 * @param $result Reflection_Property_Value[]
	 */
	public function afterListSearchValues(&$result)
	{
		if (isset($result)) {
			foreach ($result as $property) {
				if ($property instanceof Reflection_Property_Value) {
					$property->value(self::propertyToIso($property));
				}
			}
		}
	}

	//------------------------------------------------------- beforeObjectBuilderArrayBuildBasicValue
	/**
	 * @param $property Reflection_Property
	 * @param $value    boolean|integer|float|string|array
	 */
	public function beforeObjectBuilderArrayBuildBasicValue(
		Reflection_Property $property, &$value
	) {
		if (isset($value)) {
			if (is_array($value) && !empty($value)) {
				if ($property->getAnnotation("link")->value == "Collection") {
					$class = new Reflection_Class($property->getType()->getElementTypeAsString());
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
				}
			}
			else {
				$value = self::propertyToIso($property, $value);
			}
		}
	}

	//----------------------------------------------------------------------- classNameDisplayReverse
	/**
	 * @param $value string class name taken from the import array
	 */
	public function classNameDisplayReverse(&$value)
	{
		if (isset($value)) {
			$value = explode("\\", $value);
			foreach ($value as $key => $class_part) {
				$value[$key] = Names::displayToClass(self::rtr($class_part));
			}
			$value = join("\\", $value);
		}
	}

	//--------------------------------------------------------------- classNameReturnedValueToContext
	/**
	 * Sets context to returned value class name, if not null
	 *
	 * @param $result string
	 */
	public function classNameReturnedValueToContext($result)
	{
		if (isset($result)) {
			self::setContext($result);
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
	 * @param $result string
	 * @return string
	 */
	public function dateTimeReturnedValueToLocale($result)
	{
		return self::dateToLocale($result);
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
	 * @param $result string
	 * @return string
	 */
	public function floatReturnedValueToLocale($result)
	{
		return self::floatToLocale($result);
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
	 * @param $result string
	 * @return string
	 */
	public function integerReturnedValueToLocale($result)
	{
		return self::integerToLocale($result);
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
	/**
	 * @param $register Plugins\Register
	 */
	public function register(Plugins\Register $register)
	{
		$aop = $register->aop;
		// format from locale user input to ISO and standard formats
		$aop->beforeMethod(
			array('SAF\Framework\Object_Builder_Array', "buildBasicValue"),
			array($this, "beforeObjectBuilderArrayBuildBasicValue")
		);
		$aop->afterMethod(
			array('SAF\Framework\Default_List_Controller', "getSearchValues"),
			array($this, "afterListSearchValues")
		);
		// format to locale
		$aop->afterMethod(
			array('SAF\Framework\Html_Template_Functions', "toEditPropertyExtra"),
			array($this, "afterHtmlTemplateFuncsToEditPropertyExtra")
		);
		$aop->afterMethod(
			array('SAF\Framework\Reflection_Property_View', "formatDateTime"),
			array($this, "dateTimeReturnedValueToLocale")
		);
		$aop->afterMethod(
			array('SAF\Framework\Reflection_Property_View', "formatFloat"),
			array($this, "floatReturnedValueToLocale")
		);
		$aop->afterMethod(
			array('SAF\Framework\Reflection_Property_View', "formatInteger"),
			array($this, "integerReturnedValueToLocale")
		);
		// translations
		$aop->afterMethod(
			array('SAF\Framework\List_Settings', "getDefaultTitle"),
			array($this, "translateReturnedValue")
		);
		// translation/reverse of export/import procedures
		$aop->beforeMethod(
			array('SAF\Framework\Import_Array', "getClassNameFromValue"),
			array($this, "classNameDisplayReverse")
		);
		$aop->afterMethod(
			array('SAF\Framework\Import_Array', "getClassNameFromArray"),
			array($this, "classNameReturnedValueToContext")
		);
	}

	//------------------------------------------------------------------------------------------- rtr
	/**
	 * Reverse translation
	 *
	 * @param $translation           string
	 * @param $context               string
	 * @param $context_property_path string
	 * @return string
	 */
	public static function rtr($translation, $context = "", $context_property_path = "")
	{
		return Locale::current()->translations->reverse($translation, $context, $context_property_path);
	}

	//------------------------------------------------------------------------------------ setContext
	/**
	 * Set current context for translations
	 *
	 * Some hooks automatically set it : classNameDisplayReverse()
	 * Used by hooks that need it : propertiesDisplayReverse()
	 *
	 * @param $context
	 */
	public static function setContext($context)
	{
		self::$context = $context;
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

	//------------------------------------------------------------------------ translateReturnedValue
	/**
	 * Translate returned value
	 *
	 * @param $result string
	 * @return string
	 */
	public function translateReturnedValue($result)
	{
		return self::tr($result);
	}

}
