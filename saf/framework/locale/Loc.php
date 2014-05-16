<?php
namespace SAF\Framework\Locale;

use SAF\Framework\Import\Import_Array;
use SAF\Framework\Locale;
use SAF\Framework\Mapper\Object_Builder_Array;
use SAF\Framework\Plugin\Register;
use SAF\Framework\Plugin\Registerable;
use SAF\Framework\Reflection\Annotation\Property\Link_Annotation;
use SAF\Framework\Reflection\Interfaces\Reflection_Method;
use SAF\Framework\Reflection\Interfaces\Reflection_Property;
use SAF\Framework\Reflection\Reflection_Class;
use SAF\Framework\Reflection\Reflection_Property_Value;
use SAF\Framework\Reflection\Reflection_Property_View;
use SAF\Framework\Tools\Names;
use SAF\Framework\View\Html\Template\Functions;
use SAF\Framework\Widget\Data_List\Data_List_Controller;
use SAF\Framework\Widget\Data_List_Setting\Data_List_Settings;

/**
 * Locale plugin concentrates locale translation / formatting features into simple static calls
 */
class Loc implements Registerable
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
		return [$property, $property_path, $value];
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
				if ($property->getAnnotation('link')->value == Link_Annotation::COLLECTION) {
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
			$value = explode(BS, $value);
			foreach ($value as $key => $class_part) {
				$value[$key] = Names::displayToClass(self::rtr($class_part));
			}
			$value = join(BS, $value);
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
	 * @return Date_Format
	 */
	public static function date()
	{
		return Locale::current()->date_format;
	}

	//---------------------------------------------------------------------------------- dateToLocale
	/**
	 * Takes an ISO date and make it locale
	 *
	 * @param $date string ie '2001-12-25' '2001-12-25 12:20:00' '2001-12-25 12:20:16'
	 * @return string '25/12/2011' '25/12/2001 12:20' '25/12/2001 12:20:16'
	 */
	public static function dateToLocale($date)
	{
		return Locale::current()->date_format->toLocale($date);
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
	 * @param $date string ie '25/12/2001' '25/12/2001 12:20' '25/12/2001 12:20:16'
	 * @return string ie '2001-12-25' '2001-12-25 12:20:00' '2001-12-25 12:20:16'
	 */
	public static function dateToIso($date)
	{
		return Locale::current()->date_format->toIso($date);
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
		return Locale::current()->number_format->floatToIso($float, $property);
	}

	//--------------------------------------------------------------------------------- floatToLocale
	/**
	 * Takes a float number and make it locale
	 *
	 * @param $float float ie 1000 1000.28 1000.2148
	 * @return string ie '1 000,00' '1 000,28' '1 000,2148'
	 */
	public static function floatToLocale($float)
	{
		return Locale::current()->number_format->floatToLocale($float);
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
		return Locale::current()->number_format->integerToIso($integer, $property);
	}

	//------------------------------------------------------------------------------- integerToLocale
	/**
	 * Takes an integer and make it locale
	 *
	 * @param $integer integer ie 1000
	 * @return string ie '1 000'
	 */
	public static function integerToLocale($integer)
	{
		return Locale::current()->number_format->integerToLocale($integer);
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

	//-------------------------------------------------------------------------------- methodToLocale
	/**
	 * Change an ISO value into a locale formatted value, knowing it's property
	 *
	 * @param $method Reflection_Method
	 * @param $value  mixed
	 * @return string
	 */
	public static function methodToLocale(Reflection_Method $method, $value)
	{
		return Locale::current()->methodToLocale($method, $value);
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
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$aop = $register->aop;
		// format from locale user input to ISO and standard formats
		$aop->beforeMethod(
			[Object_Builder_Array::class, 'buildBasicValue'],
			[$this, 'beforeObjectBuilderArrayBuildBasicValue']
		);
		$aop->afterMethod(
			[Data_List_Controller::class, 'getSearchValues'],
			[$this, 'afterListSearchValues']
		);
		// format to locale
		$aop->afterMethod(
			[Functions::class, 'toEditPropertyExtra'],
			[$this, 'afterHtmlTemplateFuncsToEditPropertyExtra']
		);
		$aop->afterMethod(
			[Reflection_Property_View::class, 'formatBoolean'],
			[$this, 'translateReturnedValue']
		);
		$aop->afterMethod(
			[Reflection_Property_View::class, 'formatDateTime'],
			[$this, 'dateTimeReturnedValueToLocale']
		);
		$aop->afterMethod(
			[Reflection_Property_View::class, 'formatFloat'],
			[$this, 'floatReturnedValueToLocale']
		);
		$aop->afterMethod(
			[Reflection_Property_View::class, 'formatInteger'],
			[$this, 'integerReturnedValueToLocale']
		);
		$aop->afterMethod(
			[Reflection_Property_View::class, 'formatString'],
			[$this, 'translateStringPropertyView']
		);
		// translations
		$aop->afterMethod(
			[Data_List_Settings::class, 'getDefaultTitle'],
			[$this, 'translateReturnedValue']
		);
		// translation/reverse of export/import procedures
		$aop->beforeMethod(
			[Import_Array::class, 'getClassNameFromValue'],
			[$this, 'classNameDisplayReverse']
		);
		$aop->afterMethod(
			[Import_Array::class, 'getClassNameFromArray'],
			[$this, 'classNameReturnedValueToContext']
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
	public static function rtr($translation, $context = '', $context_property_path = '')
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
	public static function tr($text, $context = '')
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

	//------------------------------------------------------------------- translateStringPropertyView
	/**
	 * @param $object Reflection_Property_View
	 * @param $value  string
	 * @return string
	 */
	public function translateStringPropertyView(Reflection_Property_View $object, $value)
	{
		return ($object->property->getListAnnotation('values')->values())
			? $this->tr($value, $object->property->final_class)
			: $value;
	}

}
