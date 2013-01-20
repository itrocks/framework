<?php
namespace SAF\Framework;
use AopJoinpoint;

abstract class Loc implements Plugin
{

	//------------------------------------------------------------------------ $date_time_locale_mode
	/**
	 * When > 0, Date_Time::__toString() will parse date using current locale
	 *
	 * @var integer
	 */
	private static $date_time_locale_mode = 0;

	//--------------------------------------------------------------------------- afterDataLinkSelect
	/**
	 * When $date_time_locale mode is true, Data_Link::select return datetimes as locale
	 * $date_time_locale mode is set to true by 
	 *
	 * @param AopJoinpoint $joinpoint
	 */
	public static function afterDataLinkSelect(AopJoinpoint $joinpoint)
	{
		if (self::$date_time_locale_mode) {
			$arguments = $joinpoint->getArguments();
			$class_name = $arguments[0];
			$columns = $arguments[1];
			$dates_columns = array();
			foreach ($columns as $key => $column_name) {
				if (
					Reflection_Property::getInstanceOf($class_name, $column_name)->getType() == "Date_Time"
				) {
					$dates_columns[] = $column_name;
				}
			}
			if ($dates_columns) {
				$list = $joinpoint->getReturnedValue();
				foreach ($list->elements as $row) {
					foreach ($dates_columns as $column_name) {
						$row->values[$column_name] = self::dateToLocale($row->values[$column_name]);
					}
				}
			}
		}
	}

	//------------------------------------------------------------------------- afterDateTimeToString
	/**
	 * @param AopJoinpoint $joinpoint
	 */
	public static function afterDateTimeToString(AopJoinpoint $joinpoint)
	{
		if (self::$date_time_locale_mode) {
			$joinpoint->setReturnedValue(self::dateToLocale($joinpoint->getReturnedValue()));
		}
	}

	//--------------------------------------------------------------------------- beforeDataLinkWrite
	/**
	 * @param AopJoinpoint $joinpoint
	 */
	public static function beforeDataLinkWrite(AopJoinpoint $joinpoint)
	{
		if (self::$date_time_locale_mode) {
			$object = $joinpoint->getArguments()[0];
			$class = Reflection_Class::getInstanceOf($object);
			foreach ($class->accessProperties() as $property) {
				if ($property->getType() == "Date_Time") {
					if (is_string($value = $property->getValue($object))) {
						$property->setValue(Loc::dateToIso($value));
					}
				}
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
		if (self::$date_time_locale_mode) {
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

	//-------------------------------------------------------------------- dateTimeLocaleModeOnAround
	public static function dateTimeLocaleModeOnAround(AopJoinpoint $joinpoint)
	{
		self::$date_time_locale_mode ++;
		$joinpoint->process();
		self::$date_time_locale_mode --;
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

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		// activate date time locale mode
		Aop::add("around",
			__NAMESPACE__ . "\\Default_Write_Controller->run()",
			array(__CLASS__, "dateTimeLocaleModeOnAround")
		);
		Aop::add("around",
			__NAMESPACE__ . "\\Default_List_Controller->getViewParameters()",
			array(__CLASS__, "dateTimeLocaleModeOnAround")
		);
		Aop::add("around",
			__NAMESPACE__ . "\\Html_Template->parse()",
			array(__CLASS__, "dateTimeLocaleModeOnAround")
		);
		// on data link instructions that can pass some string date times arguments
		Aop::add("after",
			__NAMESPACE__ . "\\Data_Link->select()",
			array(__CLASS__, "afterDataLinkSelect")
		);
		Aop::add("before",
			__NAMESPACE__ . "\\Data_Link->write()",
			array(__CLASS__, "beforeDataLinkWrite")
		);
		// on date time from iso casting
		Aop::add("before",
			__NAMESPACE__ . "\\Date_Time->fromISO()",
			array(__CLASS__, "beforeDateTimeFromIso")
		);
		// on date time to string casting
		Aop::add("after",
			__NAMESPACE__ . "\\Date_Time->__toString()",
			array(__CLASS__, "afterDateTimeToString")
		);
	}

	//------------------------------------------------------------------------------------------- rtr
	public static function rtr($translation, $context = "")
	{
		return Locale::current()->translations->reverse($translation, $context);
	}

	//-------------------------------------------------------------------------------------------- tr
	public static function tr($text, $context = "")
	{
		return Locale::current()->translations->translate($text, $context);
	}

}
