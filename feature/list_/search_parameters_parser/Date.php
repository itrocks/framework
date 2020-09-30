<?php
namespace ITRocks\Framework\Feature\List_\Search_Parameters_Parser;

use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Dao\Func\Dao_Function;
use ITRocks\Framework\Dao\Option;
use ITRocks\Framework\Feature\List_\Exception;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Tools\Date_Time;

/**
 * Date search parameters parser
 * - According to locale date format day and month can be inverted d/m <-> m/d
 * - Hours are on 24h format
 * - We can not express numerical month only, minutes only or seconds only, it will be processed
 *   like a day only
 *
 * [d]d/[m]m/yyyy [h]h:[m]m:[s]s
 * [d]d/[m]m/yyyy [h]h:[m]m
 * [d]d/[m]m/yyyy [h]h
 * [d]d/[m]m/yyyy
 * [d]d/[m]m         (means implicit current year)
 * [m]m/yyyy         (means from 01/mm/yyyy to 31!/mm/yyyy) 3-4 chars mandatory
 * yyyy              (means from 01/01/yyyy to 31/12/yyyy) 3-4 chars mandatory
 * [d]d              (means implicit current month and year)
 * "y" [+|-] integer (means from 01/01/yyyy to 31/12/yyyy)
 * "m" [+|-] integer (means from 01/mm/currentyear to 31!/mm/currentyear)
 * "d" [+|-] integer (means implicit current month and year)
 * "h" [+|-] integer (means implicit current day) from 0 minute 0s to 59 minutes 59s
 *
 * Test expressions:
 * 05/03/2015 20:45:57
 * 05/03/2015 20:45
 * 05/03/2015 20
 * 05/03/2015
 * 05/03
 * 03/2015
 * 03/20*
 * 2015/03
 * 20??/03
 * 2005
 * 2*6
 * 05
 * d-1/m-2/y-3 h-1:m-1:s-2
 * d-1
 * m-2
 * y-3
 * h-1
 * 05/03/2001 h-1
 * ?/?/? ?:?:?
 * ?/?/? ?:?
 * ?/?/? ?
 * ?/?/?
 * ?/?
 * ?
 * 0/0/0 0:0:0
 * 00/00/0000 00:00:00
 * 0
 * 05/3/201*
 * 05/3/20*
 * 05/3/*
 */
abstract class Date
{

	//------------------------------------------------------------------------------------------ DATE
	const DATE = 'date';

	//------------------------------------------------------------------------------ DATE_HOUR_MINUTE
	const DATE_HOUR_MINUTE = 'date_hour_minute';

	//-------------------------------------------------------------------------- DATE_HOUR_MINUTE_ISO
	const DATE_HOUR_MINUTE_ISO = 'date_hour_minute_iso';

	//-------------------------------------------------------------------------------- DATE_HOUR_ONLY
	const DATE_HOUR_ONLY = 'date_hour_only';

	//---------------------------------------------------------------------------- DATE_HOUR_ONLY_ISO
	const DATE_HOUR_ONLY_ISO = 'date_hour_only_iso';

	//-------------------------------------------------------------------------------------- DATE_ISO
	const DATE_ISO = 'date_iso';

	//------------------------------------------------------------------------------------ DATE_PARTS
	const DATE_PARTS = [
		// The order of these values make sense
		Date_Time::DAY,
		Date_Time::MONTH,
		Date_Time::YEAR,
		Date_Time::HOUR,
		Date_Time::MINUTE,
		Date_Time::SECOND
	];

	//------------------------------------------------------------------------------------- DATE_TIME
	const DATE_TIME = 'date_time';

	//--------------------------------------------------------------------------------- DATE_TIME_ISO
	const DATE_TIME_ISO = 'date_time_iso';

	//------------------------------------------------------------------------------------- DAY_MONTH
	const DAY_MONTH = 'day_month';

	//-------------------------------------------------------------------------------------- DAY_ONLY
	const DAY_ONLY = 'day_only';

	//----------------------------------------------------------------------------------- HOUR_MINUTE
	const HOUR_MINUTE = 'hour_minute';

	//---------------------------------------------------------------------------- HOUR_MINUTE_SECOND
	const HOUR_MINUTE_SECOND = 'hour_minute_second';

	//------------------------------------------------------------------------------------- HOUR_ONLY
	const HOUR_ONLY = 'hour_only';

	//--------------------------------------------------------------------------------- KIND_OF_DATES
	/**
	 * All kind of date expression we can have.
	 */
	const KIND_OF_DATES = [
		// The order of these values make sense : please do not alter it
		self::DATE_TIME,
		self::DATE_HOUR_MINUTE,
		self::DATE_HOUR_ONLY,
		self::DATE,
		self::MONTH_YEAR,
		self::YEAR_MONTH,
		self::DAY_MONTH,
		self::YEAR_ONLY,
		self::DAY_ONLY,
		self::MONTH_ONLY, // can only be a formula like "m-1"
		self::HOUR_ONLY,  // can only be a formula like "h-1", implicit current day
		self::HOUR_MINUTE,
		self::HOUR_MINUTE_SECOND,
		self::DATE_TIME_ISO,
		self::DATE_HOUR_MINUTE_ISO,
		self::DATE_HOUR_ONLY_ISO,
		self::DATE_ISO,
		self::YEAR_MONTH_ISO,
	];

	//------------------------------------------------------------------------------------ MONTH_ONLY
	const MONTH_ONLY = 'month_only';

	//------------------------------------------------------------------------------------ MONTH_YEAR
	const MONTH_YEAR = 'month_year';

	//------------------------------------------------------------------------------------ YEAR_MONTH
	const YEAR_MONTH = 'year_month';

	//-------------------------------------------------------------------------------- YEAR_MONTH_ISO
	const YEAR_MONTH_ISO = 'year_month_iso';

	//------------------------------------------------------------------------------------- YEAR_ONLY
	const YEAR_ONLY = 'year_only';

	//---------------------------------------------------------------------------- $current_date_time
	/**
	 * @var Date_Time
	 */
	protected static $current_date_time;

	//---------------------------------------------------------------------------------- $current_day
	/**
	 * @var string|integer
	 */
	protected static $current_day;

	//--------------------------------------------------------------------------------- $current_hour
	/**
	 * @var string|integer
	 */
	protected static $current_hour;

	//------------------------------------------------------------------------------- $current_minute
	/**
	 * @var string|integer
	 */
	protected static $current_minute;

	//-------------------------------------------------------------------------------- $current_month
	/**
	 * @var string|integer
	 */
	protected static $current_month;

	//------------------------------------------------------------------------------- $current_second
	/**
	 * @var string|integer
	 */
	protected static $current_second;

	//--------------------------------------------------------------------------------- $current_year
	/**
	 * @var string|integer
	 */
	protected static $current_year;

	//---------------------------------------------------------------------------- applyDateFormatted
	/**
	 * Transform expression of a date to suitable Func
	 *
	 * @param $expression string
	 * @param $range_side integer @values Range::MAX, Range::MIN, Range::NONE
	 * @return Dao_Function|string
	 * @throws Exception
	 */
	protected static function applyDateFormatted($expression, $range_side)
	{
		if (preg_match('/^ \\s* [0]+ \\s* $/x', $expression)) {
			return Func::isNull();
		}

		$kind_of_date = self::getKindOfDate($expression);
		if (!$kind_of_date) {
			throw new Exception(
				$expression, Loc::tr('invalid date expression')
			);
		}

		$date_parts = self::getParts($expression, $kind_of_date);
		/**
		 * created by extract() as references on $date_parts values :
		 *
		 * @var $day    string
		 * @var $month  string
		 * @var $year   string
		 * @var $hour   string
		 * @var $minute string
		 * @var $second string
		 */
		extract($date_parts, EXTR_OVERWRITE|EXTR_REFS);

		if (self::isEmptyParts($date_parts)) {
			return Func::isNull();
		}

		foreach (self::DATE_PARTS as $date_part) {
			if (!self::computePart($date_parts[$date_part], $date_part)) {
				throw new Exception(
					$expression, Loc::tr('invalid date expression') . '(' . Loc::tr($date_part) . ')'
				);
			};
		}

		$has_wildcard = self::onePartHasWildcard($date_parts);
		if ($has_wildcard) {
			if ($range_side != Range::NONE) {
				throw new Exception(
					$expression, Loc::tr('You can not have wildcard on a range value of a date expression')
				);
			}
			$has_formula = self::onePartHasFormula($date_parts);
			if ($has_formula) {
				throw new Exception(
					$expression, Loc::tr('You can not combine wildcard and formula in a date expression')
				);
			}
			self::fillEmptyPartsWithWildcard($date_parts);
			self::padParts($date_parts);
			$date = Func::like("$year-$month-$day $hour:$minute:$second");
		}
		else {
			switch ($kind_of_date) {
				case self::DATE_TIME:
				case self::DATE_TIME_ISO:
					$date_begin = date('Y-m-d H:i:s', mktime($hour, $minute, $second, $month, $day, $year));
					$date_end   = date('Y-m-d H:i:s', mktime($hour, $minute, $second, $month, $day, $year));
					break;
				case self::DATE_HOUR_MINUTE:
				case self::DATE_HOUR_MINUTE_ISO:
					$date_begin = date('Y-m-d H:i:s', mktime($hour, $minute, 0 , $month, $day, $year));
					$date_end   = date('Y-m-d H:i:s', mktime($hour, $minute, 59, $month, $day, $year));
					break;
				case self::DATE_HOUR_ONLY:
				case self::DATE_HOUR_ONLY_ISO:
					$date_begin = date('Y-m-d H:i:s', mktime($hour, 0 , 0 , $month, $day, $year));
					$date_end   = date('Y-m-d H:i:s', mktime($hour, 59, 59, $month, $day, $year));
					break;
				case self::DATE:
				case self::DATE_ISO:
					$date_begin = date('Y-m-d H:i:s', mktime(0 , 0 , 0 , $month, $day, $year));
					$date_end   = date('Y-m-d H:i:s', mktime(23, 59, 59, $month, $day, $year));
					break;
				case self::MONTH_YEAR:
				case self::YEAR_MONTH:
				case self::YEAR_MONTH_ISO:
					$date_begin = date('Y-m-d H:i:s', mktime(0, 0, 0 ,      $month    , 1, $year));
					$date_end   = date('Y-m-d H:i:s', mktime(0, 0, -1, (int)$month + 1, 1, $year));
					break;
				case self::DAY_MONTH:
					$year = self::$current_year;
					$date_begin = date('Y-m-d H:i:s', mktime(0 , 0 , 0 , $month, $day, $year));
					$date_end   = date('Y-m-d H:i:s', mktime(23, 59, 59, $month, $day, $year));
					break;
				case self::YEAR_ONLY:
					$date_begin = date('Y-m-d H:i:s', mktime(0 , 0 , 0 , 1 , 1 , $year));
					$date_end   = date('Y-m-d H:i:s', mktime(23, 59, 59, 12, 31, $year));
					break;
				case self::DAY_ONLY:
					$year  = self::$current_year;
					$month = self::$current_month;
					$date_begin = date('Y-m-d H:i:s', mktime(0 , 0 , 0 , $month, $day, $year));
					$date_end   = date('Y-m-d H:i:s', mktime(23, 59, 59, $month, $day, $year));
					break;
				case self::MONTH_ONLY: // can only be a formula like "m-1"
					$year = self::$current_year;
					$date_begin = date('Y-m-d H:i:s', mktime(0, 0, 0 , $month, 1, $year));
					$date_end   = date('Y-m-d H:i:s', mktime(0, 0, -1, (int)$month + 1, 1, $year));
					break;
				case self::HOUR_ONLY: // can only be a formula like "h-1", implicit current day
					$year  = self::$current_year;
					$month = self::$current_month;
					$day   = self::$current_day;
					$date_begin = date('Y-m-d H:i:s', mktime($hour, 0 , 0 , $month, $day, $year));
					$date_end   = date('Y-m-d H:i:s', mktime($hour, 59, 59, $month, $day, $year));
					break;
				case self::HOUR_MINUTE:
					$year  = self::$current_year;
					$month = self::$current_month;
					$day   = self::$current_day;
					$date_begin = date('Y-m-d H:i:s', mktime($hour, $minute, 0 , $month, $day, $year));
					$date_end   = date('Y-m-d H:i:s', mktime($hour, $minute, 59, $month, $day, $year));
					break;
				case self::HOUR_MINUTE_SECOND:
					$year  = self::$current_year;
					$month = self::$current_month;
					$day   = self::$current_day;
					$date_begin = date('Y-m-d H:i:s', mktime($hour, $minute, $second, $month, $day, $year));
					$date_end   = date('Y-m-d H:i:s', mktime($hour, $minute, $second, $month, $day, $year));
					break;
				default:
					$date_begin = $date_end = null;
			}
			$date = self::buildDateOrPeriod($date_begin, $date_end, $range_side);
		}
		return $date;
	}

	//--------------------------------------------------------------------------- applyDateRangeValue
	/**
	 * @param $expression Option|string
	 * @param $property   Reflection_Property
	 * @param $range_side integer @values Range::MAX, Range::MIN, Range::NONE
	 * @return mixed
	 * @throws Exception
	 */
	public static function applyDateRangeValue(
		$expression, Reflection_Property $property, $range_side
	) {
		if (Wildcard::hasWildcard($expression)) {
			throw new Exception(
				$expression, Loc::tr('You can not have wildcard on a range value')
			);
		}
		return self::applyDateValue($expression, $property, $range_side);
	}

	//----------------------------------------------------------------------- applyDateSingleWildcard
	/**
	 * If expression is a single wildcard or series of wildcard chars, convert to corresponding date
	 *
	 * @param $expression string
	 * @return boolean|mixed false
	 */
	private static function applyDateSingleWildcard($expression)
	{
		if (is_string($expression) && preg_match('/^ \\s* [*%?_]+ \\s* $/x', $expression)) {
			return Func::notNull();
		}
		return false;
	}

	//-------------------------------------------------------------------------------- applyDateValue
	/**
	 * @param $expression string
	 * @param $property   Reflection_Property
	 * @param $range_side integer @values Range::MAX, Range::MIN, Range::NONE
	 * @return mixed
	 * @throws Exception
	 */
	public static function applyDateValue(
		$expression, Reflection_Property $property, $range_side = Range::NONE
	) {
		return self::applyDateSingleWildcard($expression)
			?: self::applyDateWord($expression, $range_side)
			?: Words::applyWordMeaningEmpty($expression, $property)
			?: self::applyDateFormatted($expression, $range_side);
	}

	//--------------------------------------------------------------------------------- applyDateWord
	/**
	 * If expression is a date word, convert to corresponding date
	 *
	 * @param $expression string
	 * @param $range_side integer @values Range::MAX, Range::MIN, Range::NONE
	 * @return Func\Range|string|boolean false
	 */
	private static function applyDateWord($expression, $range_side)
	{
		$word = Words::getCompressedWords([$expression])[0];

		if (in_array($word, self::getDateWordsToCompare(Date_Time::YEAR))) {
			// we convert a current year word in numeric current year period
			$date_begin = date(
				'Y-m-d H:i:s', mktime(0, 0, 0, 1, 1, self::$current_year)
			);
			$date_end = date(
				'Y-m-d H:i:s', mktime(23, 59, 59, 12, 31, self::$current_year)
			);
		}
		elseif (in_array($word, self::getDateWordsToCompare(Date_Time::MONTH))) {
			//we convert a current year word in numeric current month / current year period
			$date_begin = date(
				'Y-m-d H:i:s', mktime(0, 0, 0, self::$current_month, 1, self::$current_year)
			);
			$date_end = date(
				'Y-m-d H:i:s', mktime(0, 0, -1, self::$current_month + 1, 1, self::$current_year)
			);
		}
		elseif (in_array($word, self::getDateWordsToCompare(Date_Time::DAY))) {
			//we convert a current day word in numeric current day period
			$date_begin = date(
				'Y-m-d H:i:s',
				mktime(0, 0, 0, self::$current_month, self::$current_day, self::$current_year)
			);
			$date_end = date(
				'Y-m-d H:i:s',
				mktime(23, 59, 59, self::$current_month, self::$current_day, self::$current_year)
			);
		}
		elseif (in_array($word, self::getDateWordsToCompare('tomorrow'))) {
			//we convert a current day word in numeric current day period
			$date_begin = date(
				'Y-m-d H:i:s',
				mktime(0, 0, 0, self::$current_month, (int)self::$current_day+1, self::$current_year)
			);
			$date_end = date(
				'Y-m-d H:i:s',
				mktime(23, 59, 59, self::$current_month, (int)self::$current_day+1, self::$current_year)
			);
		}
		elseif (in_array($word, self::getDateWordsToCompare('yesterday'))) {
			//we convert a current day word in numeric current day period
			$date_begin = date(
				'Y-m-d H:i:s',
				mktime(0, 0, 0, self::$current_month, (int)self::$current_day-1, self::$current_year)
			);
			$date_end = date(
				'Y-m-d H:i:s',
				mktime(23, 59, 59, self::$current_month, (int)self::$current_day-1, self::$current_year)
			);
		}
		if (isset($date_begin) && isset($date_end)) {
			$date = self::buildDateOrPeriod($date_begin, $date_end, $range_side);
			return $date;
		}
		return false;
	}

	//----------------------------------------------------------------------------- buildDateOrPeriod
	/**
	 * Builds the correct Dao object for given begin and end date according to what we want
	 *
	 * @param $date_min   string
	 * @param $date_max   string
	 * @param $range_side integer @values Range::MAX, Range::MIN, Range::NONE
	 * @return Func\Range|string
	 */
	private static function buildDateOrPeriod($date_min, $date_max, $range_side)
	{
		if ($range_side == Range::MIN || ($date_min == $date_max)) {
			$date = $date_min;
		}
		elseif ($range_side == Range::MAX) {
			$date = $date_max;
		}
		else {
			$date = Range::buildRange($date_min, $date_max);
		}
		return $date;
	}

	//------------------------------------------------------------------------- checkDateWildcardExpr
	/**
	 * Check an expression (part of a datetime) contains wildcards and correct it, if necessary
	 *
	 * @param $expression string
	 * @param $date_part  string @values Date_Time::DAY, Date_Time::MONTH, Date_Time::YEAR,
	 *                                   Date_Time::HOUR, Date_Time::MINUTE, Date_Time::SECOND
	 * @return boolean
	 */
	public static function checkDateWildcardExpr(&$expression, $date_part)
	{
		$expression = str_replace(['*', '?'], ['%', '_'], $expression);
		$nchar      = ($date_part == Date_Time::YEAR ? 4 : 2);
		if ($c = preg_match_all("/^[0-9_%]{1,$nchar}$/", $expression)) {
			self::correctDateWildcardExpr($expression, $date_part);
			return true;
		}
		return false;
	}

	//------------------------------------------------------------------------------ checkNumericExpr
	/**
	 * Check an expression is numeric
	 *
	 * @param $expression string
	 * @return boolean
	 */
	private static function checkNumericExpr(&$expression)
	{
		return is_numeric($expression) && ((string)((int)$expression) == $expression);
	}

	//-------------------------------------------------------------------------------- computeFormula
	/**
	 * Compile a formula and compute value for a part of date
	 *
	 * @param $expression string formula
	 * @param $date_part  string @values Date_Time::DAY, Date_Time::MONTH, Date_Time::YEAR,
	 *                                   Date_Time::HOUR, Date_Time::MINUTE, Date_Time::SECOND
	 * @return boolean true if formula found
	 */
	private static function computeFormula(&$expression, $date_part)
	{
		$pp = '[' . self::getDateLetters($date_part) . ']';
		if (preg_match(
			"/^ \\s* $pp \\s* (?:(?<sign>[-+]) \\s* (?<operand>\\d+))? \\s* $/x", $expression, $matches
		)) {
			/**
			 * Notice : We take care to keep computed values as computed even if above limits
			 * (eg for a month > 12 or < 1) because we'll give result to mktime in order
			 * it may change year and/or day accordingly
			 * eg current month is 12 and formula is m+1 => mktime(0,0,0,20,13,2016) for 20/01/2017
			 */
			$f = [
				Date_Time::YEAR   => 'Y',
				Date_Time::MONTH  => 'm',
				Date_Time::DAY    => 'd',
				Date_Time::HOUR   => 'h',
				Date_Time::MINUTE => 'i',
				Date_Time::SECOND => 's'
			];
			$value = (int)(self::$current_date_time->format($f[$date_part]));
			if (isset($matches['sign']) && isset($matches['operand'])) {
				$sign       = $matches['sign'];
				$operand    = (int)($matches['operand']);
				$expression = (string)($sign == '+' ? $value + $operand : $value - $operand);
			}
			else {
				$expression = $value;
			}
			return true;
		}
		return false;
	}

	//----------------------------------------------------------------------------------- computePart
	/**
	 * Compute a date part expression to get a string suitable to build a Date
	 *
	 * @param $expression string numeric or with wildcard or formula d+1 | m+3 | y-2 | h+1 | i+3...
	 * @param $date_part  string @values Date_Time::DAY, Date_Time::MONTH, Date_Time::YEAR,
	 *                                   Date_Time::HOUR, Date_Time::MINUTE, Date_Time::SECOND
	 * @return boolean
	 */
	protected static function computePart(&$expression, $date_part)
	{
		$expression = trim($expression);
		// empty expression
		if (!strlen($expression)) {
			return true;
		}
		// numeric expr
		if (self::checkNumericExpr($expression)) {
			return true;
		}
		// expression with wildcards
		if (self::checkDateWildcardExpr($expression, $date_part)) {
			return true;
		}
		// expression with formula
		if (self::computeFormula($expression, $date_part)) {
			return true;
		}
		return false;
	}

	//----------------------------------------------------------------------- correctDateWildcardExpr
	/**
	 * Correct a date expression containing SQL wildcard in order to build a Date string
	 *
	 * @param $expression string
	 * @param $date_part  string @values Date_Time::DAY, Date_Time::MONTH, Date_Time::YEAR,
	 *                                   Date_Time::HOUR, Date_Time::MINUTE, Date_Time::SECOND
	 */
	private static function correctDateWildcardExpr(&$expression, $date_part)
	{
		/**
		 * eg. for a month or day (or hour, minutes, seconds), it's simple since we have 2 chars only
		 *
		 * %% => __
		 * %  => __
		 * 1% => 1_
		 * %2 => _2
		 * _  => __
		 * So we simply have to replace % by _ and if a single _ then __
		 */
		if ($date_part != Date_Time::YEAR) {
			$expression = str_replace('%', '_', $expression);
			if ($expression == '_') {
				$expression = '__';
			}
		}
		/**
		 * eg. for a year, it's a bit more complex. All possible combinations => correction
		 *
		 * %%%% => ____
		 * %%%  => ____
		 * %%   => ____
		 * %    => ____    use pattern #1#
		 *
		 * 2%%% => 2___
		 * 2%%  => 2___
		 * 2%   => 2___    use pattern #2#
		 *
		 * 20%% => 20__
		 * 20%  => 20__    use pattern #3#
		 *
		 * %%%6 => ___6
		 * %%6  => ___6
		 * %6   => ___6    use pattern #4#
		 *
		 * %%16 => __16
		 * %16  => __16    use pattern #5#
		 *
		 * 2%%6 => 2__6
		 * 2%6  => 2__6    use pattern #6#
		 *
		 * %016 => _016    direct replace % by _
		 * 2%16 => 2_16    direct replace % by _
		 * 20%6 => 20_6    direct replace % by _
		 * 201% => 201_    direct replace % by _
		 *
		 * %0%6 => _0_6    direct replace % by _
		 * %01% => _01_    direct replace % by _
		 * 2%1% => 2_1_    direct replace % by _
		 */
		static $patterns = [
			/* #1# */ '/^[%]{1,4}$/',
			/* #2# */ '/^([0-9_])[%]{1,3}$/',
			/* #3# */ '/^([0-9_][0-9_])[%]{1,2}$/',
			/* #4# */ '/^[%]{1,3}([0-9_])$/',
			/* #5# */ '/^[%]{1,2}([0-9_][0-9_])$/',
			/* #6# */ '/^([0-9_])[%]{1,2}([0-9_])$/'
		];
		static $replacements = [
			/* #1# */ '____',
			/* #2# */ '${1}___',
			/* #3# */ '${1}__',
			/* #4# */ '___${1}',
			/* #5# */ '__${1}',
			/* #6# */ '${1}__${2}'
		];
		$expression = preg_replace($patterns, $replacements, $expression);
		$expression = str_replace('%', '_', $expression);
	}

	//-------------------------------------------------------------------- fillEmptyPartsWithWildcard
	/**
	 * @param $date_parts string[]
	 */
	private static function fillEmptyPartsWithWildcard(array &$date_parts)
	{
		foreach ($date_parts as $date_part => $part) {
			if (!strlen($part)) {
				$date_parts[$date_part] = $date_part == Date_Time::YEAR ? '____' : '__';
			}
		}
	}

	//-------------------------------------------------------------------------------- getDateLetters
	/**
	 * Gets the letters that can be used in formula for a part of a date
	 *
	 * @param $date_part null|string Date_Time::DAY, Date_Time::MONTH, Date_Time::YEAR,
	 *                               Date_Time::HOUR, Date_Time::MINUTE, Date_Time::SECOND
	 * @return string
	 */
	private static function getDateLetters($date_part = null)
	{
		static $letters;
		if (!isset($letters)) {
			$letters = explode('|', Loc::tr('d|m|y') . '|' . Loc::tr('h|m|s'));
			$ip_up = function($letter) { return isset($letter) ? ($letter . strtoupper($letter)) : ''; };
			$letters = [
				Date_Time::DAY     => 'dD' . $ip_up(($letters[0] != 'd') ? $letters[0] : null),
				Date_Time::MONTH   => 'mM' . $ip_up(($letters[1] != 'm') ? $letters[1] : null),
				Date_Time::YEAR    => 'yY' . $ip_up(($letters[2] != 'y') ? $letters[2] : null),
				Date_Time::HOUR    => 'hH' . $ip_up(($letters[3] != 'h') ? $letters[3] : null),
				Date_Time::MINUTE  => 'mM' . $ip_up(($letters[4] != 'm') ? $letters[4] : null),
				Date_Time::SECOND  => 'sS' . $ip_up(($letters[5] != 's') ? $letters[5] : null)
			];
		}
		if (!isset($date_part)) {
			return implode('', $letters);
		}
		return $letters[$date_part];
	}

	//-------------------------------------------------------------------------------- getDatePattern
	/**
	 * Gets the PCRE Pattern of a date that may contain formula in its part
	 *
	 * e.g 1/m-1 | 1/m+2/y-1 | d-7 | ...
	 * Note: this is not the complete pattern, you should surround by delimiters
	 * and add whatever else you want
	 *
	 * @param $get_named    boolean true if want named pattern
	 * @param $kind_of_date null|string value of self::KIND_OF_DATE
	 * @return string
	 */
	public static function getDatePattern($get_named, $kind_of_date = null)
	{
		static $big_named_pattern   = null;
		static $big_unnamed_pattern = null;
		static $named_patterns      = null;
		static $unnamed_patterns    = null;
		if (!isset($big_named_pattern)) {
			$y_letters = self::getDateLetters(Date_Time::YEAR);
			$m_letters = self::getDateLetters(Date_Time::MONTH);
			$d_letters = self::getDateLetters(Date_Time::DAY);
			$h_letters = self::getDateLetters(Date_Time::HOUR);
			$i_letters = self::getDateLetters(Date_Time::MINUTE);
			$s_letters = self::getDateLetters(Date_Time::SECOND);

			// pattern for a date part : digits with optional wildcards or formula
			$day        = '(?:[' . $d_letters . '](?:[-+]\d+)?) | (?:[0-3*?%_]?[0-9*?%_])';
			$month      = '(?:[' . $m_letters . '](?:[-+]\d+)?) | (?:[0-1*?%_]?[0-9*?%_])';
			$month_only = '(?:[' . $m_letters . '](?:[-+]\d+)?)';

			// formula | 4 digits | 3 to 4 digit with wildcard if preceded by '/'
			$year = '(?:[' . $y_letters . '](?:[-+]\d+)?) | [0-9]{4} | (?<=\\/)[0-9*?%_]{3,4} '
				// | 3 to 4 digit with wildcard if followed by '/'
				. '| [0-9*?%_]{3,4}(?=\\/) '
				// | 1 to 4 wildcards | 1 to 4 '0' only if preceded by '/'
				. '| [*?%_]{1,4} | (?<=\\/)0{1,4}';

			// formula | 4 digits | 3 to 4 digit with wildcard if preceded by '-'
			$year_iso   = '(?:[' . $y_letters . '](?:[-+]\d+)?) | [0-9]{4} | (?<=-)[0-9*?%_]{3,4} '
				// | 3 to 4 digit with wildcard if followed by '-'
				. '| [0-9*?%_]{3,4}(?=-) '
				// | 1 to 4 wildcards | 1 to 4 '0' only if followed by '-'
				. '| [*?%_]{1,4} | (?=-)0{1,4}';

			// formula | 3 to 4 digit with wildcard
			$year_only = '(?:[' . $y_letters . '](?:[-+]\d+)?) | [0-9*?%_]{3,4}';
			$hour      = '(?:[' . $h_letters . '](?:[-+]\d+)?) | (?:[0-2*?%_]?[0-9*?%_])';
			$hour_only = '(?:[' . $h_letters . '](?:[-+]\d+)?)';
			$minute    = '(?:[' . $i_letters . '](?:[-+]\d+)?) | (?:[0-5*?%_]?[0-9*?%_])';
			$second    = '(?:[' . $s_letters . '](?:[-+]\d+)?) | (?:[0-5*?%_]?[0-9*?%_])';

			//build the named patterns that helps to split an expression in many parts
			$named = [];
			$named['day']        = "(?P<" . Date_Time::DAY    . "> $day )";
			$named['month']      = "(?P<" . Date_Time::MONTH  . "> $month )";
			$named['month_only'] = "(?P<" . Date_Time::MONTH  . "> $month_only )";
			$named['year']       = "(?P<" . Date_Time::YEAR   . "> $year )";
			$named['year_iso']   = "(?P<" . Date_Time::YEAR   . "> $year_iso )";
			$named['year_only']  = "(?P<" . Date_Time::YEAR   . "> $year_only )";
			$named['hour']       = "(?P<" . Date_Time::HOUR   . "> $hour )";
			$named['hour_only']  = "(?P<" . Date_Time::HOUR   . "> $hour_only )";
			$named['minute']     = "(?P<" . Date_Time::MINUTE . "> $minute )";
			$named['second']     = "(?P<" . Date_Time::SECOND . "> $second )";
			$named_patterns      = self::getDatePatternsArray($named);

			// build unnamed patterns for a big pattern (we can not have same name twice in a pattern)
			$unnamed = [];
			$unnamed['day']        = "(?: $day )";
			$unnamed['month']      = "(?: $month )";
			$unnamed['month_only'] = "(?: $month_only )";
			$unnamed['year']       = "(?: $year )";
			$unnamed['year_iso']   = "(?: $year_iso )";
			$unnamed['year_only']  = "(?: $year_only )";
			$unnamed['hour']       = "(?: $hour )";
			$unnamed['hour_only']  = "(?: $hour_only )";
			$unnamed['minute']     = "(?: $minute )";
			$unnamed['second']     = "(?: $second )";
			$unnamed_patterns      = self::getDatePatternsArray($unnamed);

			//build the big pattern that check if expression is a date and can get kind of date
			$big_named_pattern_parts   = [];
			$big_unnamed_pattern_parts = [];
			foreach (self::KIND_OF_DATES as $kind) {
				$big_named_pattern_parts[$kind]   = "(?P<" . $kind . ">" . $unnamed_patterns[$kind] . ")";
				$big_unnamed_pattern_parts[$kind] = "(?:" . $unnamed_patterns[$kind] . ")";
			}
			// You wanna debug? copy this regexp : /^ \s* $big_named_pattern \s* $/gmx
			// into https://regex101.com/ and try your dates
			$big_named_pattern = "(?: " . LF . TAB . SP . SP
				. implode(LF . TAB . '| ', $big_named_pattern_parts) . LF . " )";
			$big_unnamed_pattern = "(?: " . LF . TAB . SP . SP
				. implode(LF . TAB . '| ', $big_unnamed_pattern_parts) . LF . " )";
		}

		if (isset($kind_of_date)) {
			if ($get_named) {
				return $named_patterns[$kind_of_date];
			}
			return $unnamed_patterns[$kind_of_date];
		}
		if ($get_named) {
			return $big_named_pattern;
		}
		return $big_unnamed_pattern;
	}

	//-------------------------------------------------------------------------- getDatePatternsArray
	/**
	 * @param $sub_patterns string[]
	 * @return string[]
	 */
	private static function getDatePatternsArray(array $sub_patterns)
	{
		/**
		 * @var $day        string
		 * @var $month      string
		 * @var $month_only string
		 * @var $year       string
		 * @var $year_iso   string
		 * @var $year_only  string
		 * @var $hour       string
		 * @var $hour_only  string
		 * @var $minute     string
		 * @var $second     string
		 */
		extract($sub_patterns);
		$patterns = [];

		$patterns[self::DATE_TIME_ISO]      = "(?:$year_iso-$month-$day \\s $hour\\:$minute\\:$second)";
		$patterns[self::DATE_HOUR_MINUTE_ISO] = "(?:$year_iso-$month-$day \\s $hour\\:$minute)";
		$patterns[self::DATE_HOUR_ONLY_ISO]   = "(?:$year_iso-$month-$day \\s $hour)";
		$patterns[self::DATE_ISO]             = "(?:$year_iso-$month-$day)";
		$patterns[self::YEAR_MONTH_ISO]       = "(?:$year-$month)";
		// d/m/Y
		if (Loc::date()->format == 'd/m/Y') {
			$patterns[self::DATE_TIME]        = "(?:$day\\/$month\\/$year \\s $hour\\:$minute\\:$second)";
			$patterns[self::DATE_HOUR_MINUTE] = "(?:$day\\/$month\\/$year \\s $hour\\:$minute)";
			$patterns[self::DATE_HOUR_ONLY]   = "(?:$day\\/$month\\/$year \\s $hour)";
			$patterns[self::DATE]             = "(?:$day\\/$month\\/$year)";
			$patterns[self::DAY_MONTH]        = "(?:$day\\/$month)";
		}
		// m/d/Y
		else {
			$patterns[self::DATE_TIME]        = "(?:$month\\/$day\\/$year \\s $hour\\:$minute\\:$second)";
			$patterns[self::DATE_HOUR_MINUTE] = "(?:$month\\/$day\\/$year \\s $hour\\:$minute)";
			$patterns[self::DATE_HOUR_ONLY]   = "(?:$month\\/$day\\/$year \\s $hour)";
			$patterns[self::DATE]             = "(?:$month\\/$day\\/$year)";
			$patterns[self::DAY_MONTH]        = "(?:$month\\/$day)";
		}
		$patterns[self::MONTH_YEAR]         = "(?:$month\\/$year)";
		$patterns[self::YEAR_MONTH]         = "(?:$year\\/$month)";
		$patterns[self::YEAR_ONLY]          = "$year_only";
		$patterns[self::DAY_ONLY]           = "$day";
		$patterns[self::MONTH_ONLY]         = "$month_only";
		$patterns[self::HOUR_ONLY]          = "$hour_only";
		$patterns[self::HOUR_MINUTE]        = "(?:$hour\\:$minute)";
		$patterns[self::HOUR_MINUTE_SECOND] = "(?:$hour\\:$minute\\:$second)";

		return $patterns;
	}

	//------------------------------------------------------------------------- getDateWordsToCompare
	/**
	 * get the words to compare with a date word in search expression
	 *
	 * @param $date_part string Date_Time::DAY, Date_Time::MONTH, Date_Time::YEAR, Date_Time::HOUR
	 * @return string[]
	 */
	private static function getDateWordsToCompare($date_part)
	{
		static $all_words_references = [
			Date_Time::DAY   => ['current day',   'today'],
			Date_Time::MONTH => ['current month', 'month'],
			Date_Time::YEAR  => ['current year',  'year'],
			Date_Time::HOUR  => ['current hour',  'hour'],
			'tomorrow'       => ['tomorrow'],
			'yesterday'      => ['yesterday']
		];
		static $all_words_localized = [];
		if (!$all_words_localized) {
			foreach($all_words_references as $dp => $words_references) {
				$all_words_localized[$dp] = [];
				foreach($words_references as $word) {
					$all_words_localized[$dp][] = Loc::tr($word);
				}
			}
		}
		$words_references = $all_words_references[$date_part];
		$words_localized  = $all_words_localized[$date_part];
		return Words::getCompressedWords(array_merge($words_references, $words_localized));
	}

	//--------------------------------------------------------------------------------- getKindOfDate
	/**
	 * Return matches of regexp cutting date expression in multiple parts
	 *
	 * @param $expression string
	 * @return string|null value of self::KIND_OF_DATE
	 */
	private static function getKindOfDate($expression)
	{
		$pattern = "/^ \\s* " . self::getDatePattern(true) . " \\s* $/x";
		if (preg_match($pattern, $expression, $matches)) {
			foreach(self::KIND_OF_DATES as $kind_of_date) {
				if (isset($matches[$kind_of_date]) && !empty($matches[$kind_of_date])) {
					return $kind_of_date;
				}
			}
		}
		return null;
	}

	//-------------------------------------------------------------------------------------- getParts
	/**
	 * @param $expression   string
	 * @param $kind_of_date string value of self::KIND_OF_DATE
	 * @return string[]|null
	 */
	private static function getParts($expression, $kind_of_date)
	{
		$pattern = "/^ \\s* " . self::getDatePattern(true, $kind_of_date) . " \\s* $/x";
		if (preg_match($pattern, $expression, $matches)) {
			$parts = [];
			foreach (self::DATE_PARTS as $date_part) {
				$parts[$date_part] = (isset($matches[$date_part]) ? $matches[$date_part] : '');
			}
			return $parts;
		}
		return null;
	}

	//------------------------------------------------------------------------------------- initDates
	/**
	 * Init dates constants
	 *
	 * @param $date Date_Time|null
	 */
	public static function initDates($date = null)
	{
		if (!isset($date)) {
			$date = Date_Time::now();
		}
		self::$current_date_time = $date;
		self::$current_year      = self::$current_date_time->format('Y');
		self::$current_month     = self::$current_date_time->format('m');
		self::$current_day       = self::$current_date_time->format('d');
		self::$current_hour      = self::$current_date_time->format('H');
		self::$current_minute    = self::$current_date_time->format('i');
		self::$current_second    = self::$current_date_time->format('s');
	}

	//---------------------------------------------------------------------------------- isEmptyParts
	/**
	 * @param $date_parts string[]
	 * @return boolean
	 */
	private static function isEmptyParts(array $date_parts)
	{
		foreach ($date_parts as $date_part => $part) {
			if (strlen($part) && !preg_match('/^ \\s* [0]+ \\s* $/x', $part)) {
				return false;
			}
		}
		return true;
	}

	//------------------------------------------------------------------------ isSingleDateExpression
	/**
	 * Check if expression if a single date containing a formula
	 *
	 * @param $expression string
	 * @return boolean
	 */
	public static function isSingleDateExpression($expression)
	{
		// we check if $expr is a single date containing formula
		// but it may be a range with 2 dates containing formula, what should return false
		// so the use of /^ ... $/
		$kind_of_date = self::getKindOfDate($expression);
		$is           = isset($kind_of_date) ? true	: false;
		return $is;
	}

	//----------------------------------------------------------------------------- onePartHasFormula
	/**
	 * @param $date_parts string[]
	 * @return boolean
	 */
	private static function onePartHasFormula(array $date_parts)
	{
		$letters = self::getDateLetters();
		foreach ($date_parts as $date_part => $part) {
			if (strpbrk($part, $letters) !== false) {
				return true;
			}
		}
		return false;
	}

	//---------------------------------------------------------------------------- onePartHasWildcard
	/**
	 * @param $date_parts string[]
	 * @return boolean
	 */
	private static function onePartHasWildcard(array $date_parts)
	{
		foreach ($date_parts as $date_part => $part) {
			if (Wildcard::hasWildcard($part)) {
				return true;
			}
		}
		return false;
	}

	//-------------------------------------------------------------------------------------- padParts
	/**
	 * Pad the date parts to have left leading 0
	 * Note: if $hours is given so $minutes and $seconds should be given too!
	 *
	 * @param $date_parts string[]
	 */
	private static function padParts(array &$date_parts)
	{
		foreach ($date_parts as $date_part => &$part) {
			$length = ($date_part == Date_Time::YEAR) ? 4 : 2;
			$part   = str_pad($part, $length, '0', STR_PAD_LEFT);
		}
	}

}
