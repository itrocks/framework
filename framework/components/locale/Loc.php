<?php
namespace SAF\Framework;
use AopJoinpoint;

abstract class Loc implements Plugin
{

	//--------------------------------------------------------------------------- $parse_before_write
	/**
	 * When > 0, data written with Dao will be parsed as an user input before being written
	 *
	 * @var integer
	 */
	private static $parse_before_write = 0;

	//----------------------------------------------------- afterHtmlTemplateFuncsToEditPropertyExtra
	/**
	 * @param AopJoinpoint $joinpoint
	 */
	public static function afterHtmlTemplateFuncsToEditPropertyExtra(AopJoinpoint $joinpoint)
	{
		$result = $joinpoint->getReturnedValue();
		$result[2] = self::propertyToLocale($result[0], $result[2]);
		$joinpoint->setReturnedValue($result);
	}

	//------------------------------------------------------------------------- afterListSearchValues
	/**
	 * @param AopJoinpoint $joinpoint
	 */
	public static function afterListSearchValues(AopJoinpoint $joinpoint)
	{
		$search = $joinpoint->getReturnedValue();
		if (isset($search)) {
			$class_name = $joinpoint->getArguments()[0];
			foreach ($search as $property) {
				$property->value(self::propertyToIso($property));
			}
			$joinpoint->setReturnedValue($search);
		}
	}

	//--------------------------------------------------------------------------- beforeDataLinkWrite
	/**
	 * @param AopJoinpoint $joinpoint
	 */
	public static function beforeDataLinkWrite(AopJoinpoint $joinpoint)
	{
		if (self::$parse_before_write) {
			$object = $joinpoint->getArguments()[0];
			$class = Reflection_Class::getInstanceOf($object);
			foreach ($class->accessProperties() as $property) {
				$type = $property->getType();
				$property->setValue(
					$object,
					self::propertyToIso($property, $property->getValue($object))
				);
			}
			$class->accessPropertiesDone();
		}
	}

	//------------------------------------------------------------------------- beforeDateTimeFromIso
	/**
	 * @param AopJoinpoint $joinpoint
	 */
	public static function beforeDateTimeFromIso(AopJoinpoint $joinpoint)
	{
		if (self::$parse_before_write) {
			$args = $joinpoint->getArguments();
			$date = $args[0];
			if (
				((strlen($date) != 10) && (strlen($date) != 19))
				|| ($date[4] != "-") || ($date[7] != "-")
			) {
				$args[0] = self::dateToIso($date);
				$joinpoint->setArguments($args);
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
	 * @param string $date ie "2001-12-25" "2001-12-25 12:20:00" "2001-12-25 12:20:16"
	 * @return string "25/12/2011" "25/12/2001 12:20" "25/12/2001 12:20:16"
	 */
	public static function dateToLocale($date)
	{
		return Locale::current()->date->toLocale($date);
	}

	//----------------------------------------------------------------- dateTimeReturnedValueToLocale
	/**
	 * @param AopJoinpoint $joinpoint
	 */
	public static function dateTimeReturnedValueToLocale(AopJoinpoint $joinpoint)
	{
		$joinpoint->setReturnedValue(self::dateToLocale($joinpoint->getReturnedValue()));
	}

	//------------------------------------------------------------------------------------- dateToIso
	/**
	 * Takes a locale date and make it ISO
	 *
	 * @param string $date ie "25/12/2001" "25/12/2001 12:20" "25/12/2001 12:20:16"
	 * @return string ie "2001-12-25" "2001-12-25 12:20:00" "2001-12-25 12:20:16"
	 */
	public static function dateToIso($date)
	{
		return Locale::current()->date->toIso($date);
	}

	//-------------------------------------------------------------------- floatReturnedValueToLocale
	/**
	 * @param AopJoinpoint $joinpoint
	 */
	public static function floatReturnedValueToLocale(AopJoinpoint $joinpoint)
	{
		$joinpoint->setReturnedValue(self::floatToLocale($joinpoint->getReturnedValue()));
	}

	//------------------------------------------------------------------------------------ floatToIso
	/**
	 * @param float $float
	 * @param Reflection_Property $property
	 */
	public static function floatToIso($float, Reflection_Property $property = null)
	{
		return Locale::current()->number->floatToIso($float, $property);
	}

	//--------------------------------------------------------------------------------- floatToLocale
	/**
	 * Takes a float number and make it locale
	 *
	 * @param float $number ie 1000 1000.28 1000.2148
	 * @return string ie "1 000,00" "1 000,28" "1 000,2148"
	 */
	public static function floatToLocale($float)
	{
		return Locale::current()->number->floatToLocale($float);
	}

	//------------------------------------------------------------------ integerReturnedValueToLocale
	/**
	 * @param AopJoinpoint $joinpoint
	 */
	public static function integerReturnedValueToLocale(AopJoinpoint $joinpoint)
	{
		$joinpoint->setReturnedValue(self::integerToLocale($joinpoint->getReturnedValue()));
	}

	//---------------------------------------------------------------------------------- integerToIso
	/**
	 * @param integer $integer
	 * @param Reflection_Property $property
	 */
	public static function integerToIso($integer, Reflection_Property $property = null)
	{
		return Locale::current()->number->integerToIso($integer, $property);
	}

	//------------------------------------------------------------------------------- integerToLocale
	/**
	 * Takes an integer and make it locale
	 *
	 * @param integer $number ie 1000
	 * @return string ie "1 000"
	 */
	public static function integerToLocale($float)
	{
		return Locale::current()->number->integerToLocale($float);
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

	//------------------------------------------------------------------------ parseBeforeWriteAround
	/**
	 * @param AopJoinpoint $joinpoint
	 */
	public static function parseBeforeWriteAround(AopJoinpoint $joinpoint)
	{
		self::$parse_before_write ++;
		$joinpoint->process();
		self::$parse_before_write --;
	}

	//--------------------------------------------------------------------------------- propertyToIso
	/**
	 * Change a locale value into an ISO formatted value, knowing it's property
	 *
	 * @param Reflection_Property $property
	 * @param string $value
	 */
	public static function propertyToIso(Reflection_Property $property, $value = null)
	{
		return Locale::current()->propertyToIso($property, $value);
	}

	//------------------------------------------------------------------------------ propertyToLocale
	/**
	 * Change an ISO value into a locale formatted value, knowing it's property
	 *
	 * @param Reflection_Property $property
	 * @param string $value
	 */
	public static function propertyToLocale(Reflection_Property $property, $value = null)
	{
		return Locale::current()->propertyToLocale($property, $value);
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		// format from locale user input to ISO and standard formats
		Aop::add("after",
			__NAMESPACE__ . "\\Default_List_Controller->getSearchValues()",
			array(__CLASS__, "afterListSearchValues")
		);
		Aop::add("around",
			__NAMESPACE__ . "\\Default_Write_Controller->run()",
			array(__CLASS__, "parseBeforeWriteAround")
		);
		Aop::add("before",
			__NAMESPACE__ . "\\Data_Link->write()",
			array(__CLASS__, "beforeDataLinkWrite")
		);
		Aop::add("before",
			__NAMESPACE__ . "\\Date_Time->fromISO()",
			array(__CLASS__, "beforeDateTimeFromIso")
		);
		// format to locale
		Aop::add("after",
			__NAMESPACE__ . "\\Html_Template_Funcs->toEditPropertyExtra()",
			array(__CLASS__, "afterHtmlTemplateFuncsToEditPropertyExtra")
		);
		Aop::add("after",
			__NAMESPACE__ . "\\Reflection_Property_View->formatDateTime()",
			array(__CLASS__, "dateTimeReturnedValueToLocale")
		);
		Aop::add("after",
			__NAMESPACE__ . "\\Reflection_Property_View->formatFloat()",
			array(__CLASS__, "floatReturnedValueToLocale")
		);
		Aop::add("after",
			__NAMESPACE__ . "\\Reflection_Property_View->formatInteger()",
			array(__CLASS__, "integerReturnedValueToLocale")
		);
	}

	//------------------------------------------------------------------------------------------- rtr
	/**
	 * Reverse translation
	 *
	 * @param string $translation
	 * @param string $context
	 */
	public static function rtr($translation, $context = "")
	{
		return Locale::current()->translations->reverse($translation, $context);
	}

	//-------------------------------------------------------------------------------------------- tr
	/**
	 * Translation
	 *
	 * @param string $text
	 * @param string $context
	 */
	public static function tr($text, $context = "")
	{
		return Locale::current()->translations->translate($text, $context);
	}

}
