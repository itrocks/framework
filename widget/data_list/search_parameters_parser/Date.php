<?php
namespace ITRocks\Framework\Widget\Data_List\Search_Parameters_Parser;

use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Dao\Func\Comparison;
use ITRocks\Framework\Dao\Option;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Widget\Data_List\Data_List_Exception;

/**
 * Date search parameters parser
 *
 * @extends Search_Parameter_Parser
 */
abstract class Date
{

	//------------------------------------------------------------------------------ $currentDateTime
	/**
	 * @var Date_Time
	 */
	protected static $currentDateTime;

	//----------------------------------------------------------------------------------- $currentDay
	/**
	 * @var string|integer
	 */
	protected static $currentDay;

	//---------------------------------------------------------------------------------- $currentHour
	/**
	 * @var string|integer
	 */
	protected static $currentHour;

	//------------------------------------------------------------------------------- $currentMinutes
	/**
	 * @var string|integer
	 */
	protected static $currentMinutes;

	//--------------------------------------------------------------------------------- $currentMonth
	/**
	 * @var string|integer
	 */
	protected static $currentMonth;

	//------------------------------------------------------------------------------- $currentSeconds
	/**
	 * @var string|integer
	 */
	protected static $currentSeconds;

	//---------------------------------------------------------------------------------- $currentYear
	/**
	 * @var string|integer
	 */
	protected static $currentYear;

	//------------------------------------------------------------------------------- applyDatePeriod
	/**
	 * @param $search_value string
	 * @param $min_max      integer @values Range::MAX_RANGE_VALUE, Range::MIN_RANGE_VALUE,
	 *                                      Range::NOT_A_RANGE_VALUE
	 * @return mixed
	 */
	public static function applyDatePeriod($search_value
		, $min_max = Range::NOT_A_RANGE_VALUE)
	{
		return self::applyDateSingleJoker($search_value)
			?: self::applyDateWord($search_value, $min_max)
			?: Words::applyEmptyWord($search_value)
			?: self::applyDayMonthYear($search_value, $min_max)
			?: self::applyMonthYear($search_value, $min_max)
			?: self::applyDayMonth($search_value, $min_max)
			?: self::applyYearOnly($search_value, $min_max)
			?: self::applyDayOnly($search_value, $min_max)
			?: self::applySingleFormula($search_value, $min_max, Date_Time::YEAR)
			?: self::applySingleFormula($search_value, $min_max, Date_Time::MONTH)
			?: self::applySingleFormula($search_value, $min_max, Date_Time::DAY);
	}

	//--------------------------------------------------------------------------- applyDateRangeValue
	/**
	 * @param $search_value string|Option
	 * @param $min_max      integer @values Range::MAX_RANGE_VALUE, Range::MIN_RANGE_VALUE,
	 *                                      Range::NOT_A_RANGE_VALUE
	 * @return mixed
	 * @throws Data_List_Exception
	 */
	public static function applyDateRangeValue($search_value, $min_max)
	{
		if (Joker::hasJoker($search_value)) {
			throw new Data_List_Exception(
				$search_value, Loc::tr('You can not have a wildcard on a range value')
			);
		}
		return self::applyDatePeriod($search_value, $min_max);
	}

	//-------------------------------------------------------------------------- applyDateSingleJoker
	/**
	 * If expression is a single wildcard or series of wildcard chars, convert to corresponding date
	 *
	 * @param $expr         string
	 * @return boolean|mixed false
	 */
	protected static function applyDateSingleJoker($expr)
	{
		if (is_string($expr) && preg_match('/^ [*%?_]+ $/x', $expr)) {
			//return Func::like("____-__-__ __:__:__");
			// Optimization by replacing LIKE by IS NOT NULL
			return Func::notNull();
		}
		return false;
	}

	//--------------------------------------------------------------------------------- applyDateWord
	/**
	 * If expression is a date word, convert to corresponding date
	 * @param $expr    string
	 * @param $min_max integer @values Range::MAX_RANGE_VALUE, Range::MIN_RANGE_VALUE,
	 *                                 Range::NOT_A_RANGE_VALUE
	 * @return mixed|boolean false
	 */
	protected static function applyDateWord($expr, $min_max)
	{
		$word = Words::getCompressedWords([$expr])[0];

		if (in_array($word, self::getDateWordsToCompare(Date_Time::YEAR))) {
			// we convert a current year word in numeric current year period
			$date_begin = date(
				'Y-m-d H:i:s', mktime(0, 0, 0, 1, 1, self::$currentYear)
			);
			$date_end = date(
				'Y-m-d H:i:s', mktime(23, 59, 59, 12, 31, self::$currentYear)
			);
		}
		elseif (in_array($word, self::getDateWordsToCompare(Date_Time::MONTH))) {
			//we convert a current year word in numeric current month / current year period
			$date_begin = date(
				'Y-m-d H:i:s', mktime(0, 0, 0, self::$currentMonth, 1, self::$currentYear)
			);
			$date_end = date(
				'Y-m-d H:i:s', mktime(0, 0, -1, self::$currentMonth + 1, 1, self::$currentYear)
			);
		}
		elseif (in_array($word, self::getDateWordsToCompare(Date_Time::DAY))) {
			//we convert a current day word in numeric current day period
			$date_begin = date(
				'Y-m-d H:i:s', mktime(0, 0, 0, self::$currentMonth, self::$currentDay, self::$currentYear)
			);
			$date_end = date(
				'Y-m-d H:i:s',
				mktime(23, 59, 59, self::$currentMonth, self::$currentDay, self::$currentYear)
			);
		}
		elseif (in_array($word, self::getDateWordsToCompare('yesterday'))) {
			//we convert a current day word in numeric current day period
			$date_begin = date(
				'Y-m-d H:i:s', mktime(0, 0, 0, self::$currentMonth, (int)self::$currentDay-1, self::$currentYear)
			);
			$date_end = date(
				'Y-m-d H:i:s',
				mktime(23, 59, 59, self::$currentMonth, (int)self::$currentDay-1, self::$currentYear)
			);
		}
		if (isset($date_begin) && isset($date_end)) {
			$date = self::buildDateOrPeriod($date_begin, $date_end, $min_max);
			return $date;
		}
		return false;
	}

	//--------------------------------------------------------------------------------- applyDayMonth
	/**
	 * Apply if expression is a day/month or month/day
	 *
	 * @param $expression string
	 * @param $min_max    integer @values Range::MAX_RANGE_VALUE, Range::MIN_RANGE_VALUE,
	 *                                    Range::NOT_A_RANGE_VALUE
	 * @return mixed|boolean false
	 * @throws Data_List_Exception
	 */
	protected static function applyDayMonth($expression, $min_max)
	{
		// two values with a middle slash
		if (substr_count($expression, SL) == 1) {
			list($one, $two) = explode(SL, $expression);
			// these should be small numbers : day/month or month/day, depending on the locale format
			if (Loc::date()->format == 'd/m/Y') {
				// day/month
				$day   = $one;
				$month = $two;
			}
			else {
				// month/day
				$day   = $two;
				$month = $one;
			}
			if (!self::computeDay($day)) {
				// bad expression ?
				throw new Data_List_Exception($expression, Loc::tr('Error in day expression'));
			}
			if (!self::computeMonth($month)) {
				// bad expression?
				throw new Data_List_Exception($expression, Loc::tr('Error in month expression'));
			}
			$date = self::buildDayMonth($day, $month, $min_max, $expression);
			return $date;
		}
		return false;
	}

	//----------------------------------------------------------------------------- applyDayMonthYear
	/**
	 * Apply if expression is a day/month/year or month/day/year
	 *
	 * @param $expr    string
	 * @param $min_max integer @values Range::MAX_RANGE_VALUE, Range::MIN_RANGE_VALUE,
	 *                                 Range::NOT_A_RANGE_VALUE
	 * @return mixed|bool false
	 * @throws Data_List_Exception
	 */
	protected static function applyDayMonthYear($expr, $min_max)
	{
		// three values with a middle slash
		if (substr_count($expr, SL) == 2) {
			list($one, $two, $three) = explode(SL, $expr);
			if (Loc::date()->format == 'd/m/Y') {
				// day/month/year
				$day   = $one;
				$month = $two;
				$year  = $three;
			}
			else {
				// month/day/year
				$day   = $two;
				$month = $one;
				$year  = $three;
			}
			if (!self::computeDay($day)) {
				// bad expression ?
				throw new Data_List_Exception($expr, Loc::tr('Error in day expression'));
			}
			if (!self::computeMonth($month)) {
				// bad expression ?
				throw new Data_List_Exception($expr, Loc::tr('Error in month expression'));
			}
			if (!self::computeYear($year)) {
				// bad expression ?
				throw new Data_List_Exception($expr, Loc::tr('Error in year expression'));
			}
			return self::buildDayMonthYear($day, $month, $year, $min_max, $expr);
		}
		return false;
	}

	//---------------------------------------------------------------------------------- applyDayOnly
	/**
	 * Apply if expression is a day only
	 *
	 * @param $expression string
	 * @param $min_max    integer @values Range::MAX_RANGE_VALUE, Range::MIN_RANGE_VALUE,
	 *                                    Range::NOT_A_RANGE_VALUE
	 * @return boolean|mixed false
	 * @throws Data_List_Exception
	 */
	protected static function applyDayOnly($expression, $min_max)
	{
		// two chars or a single joker or formula
		$letters_day = self::getDateLetters(Date_Time::DAY);
		if (preg_match(
			'/^ \s* ([*%?_] | [0-9*?%_]{1,2} | ([' . $letters_day . ']([-+]\d+)?)) \s* $/x', $expression
		)) {
			$day = $expression;
			if (!self::computeDay($day)) {
				// bad expression ?
				throw new Data_List_Exception($expression, Loc::tr('Error in day expression'));
			}
			if (Joker::hasJoker($day)) {
				list($day, $month, $year) = self::padDateParts(
					$day, self::$currentMonth, self::$currentYear
				);
				$date = Func::like("$year-$month-$day __:__:__");
			}
			elseif (!(int)$day) {
				$date = Func::isNull();
			}
			else {
				$date_begin = date(
					'Y-m-d H:i:s', mktime(0, 0, 0, self::$currentMonth, $day, self::$currentYear)
				);
				$date_end = date(
					'Y-m-d H:i:s', mktime(23, 59, 59, self::$currentMonth, $day, self::$currentYear)
				);
				$date = self::buildDateOrPeriod($date_begin, $date_end, $min_max);
			}
			return $date;
		}
		return false;
	}

	//-------------------------------------------------------------------------------- applyMonthYear
	/**
	 * Apply if expression is a month/year
	 *
	 * @param $expression string
	 * @param $min_max    integer @values Range::MAX_RANGE_VALUE, Range::MIN_RANGE_VALUE,
	 *                                    Range::NOT_A_RANGE_VALUE
	 * @return mixed|boolean false
	 * @throws Data_List_Exception
	 */
	protected static function applyMonthYear($expression, $min_max)
	{
		$letters_month = self::getDateLetters(Date_Time::MONTH);
		$letters_year  = self::getDateLetters(Date_Time::YEAR);
		// two values with a middle slash
		if (substr_count($expression, SL) == 1) {
			list($one, $two) = explode(SL, $expression);
			if (
				(strlen($one) > 2 && !preg_match('/^ \s* [' . $letters_month . ']([-+]\d+)? $/x', $one))
				|| preg_match('/^ \s* [' . $letters_year . ']([-+]\d+)? $/x', $one)
			) {
				// the first number is a year or contains 'y' or 'a' : year/month
				$month = $two;
				$year  = $one;
			}
			elseif (
				(strlen($two) > 2 && !preg_match('/^ \s* [' . $letters_month . ']([-+]\d+)? $/x', $two))
				|| preg_match('/^ [' . $letters_year . ']([-+]\d+)? \s* $/x', $two)
			) {
				// the second number is a year or contains 'y' or 'a' : month/year
				$month = $one;
				$year  = $two;
			}
			else {
				// else, may be day/month or month/day => supported elsewhere
				return false;
			}
			if (!self::computeMonth($month)) {
				// bad expression ?
				throw new Data_List_Exception($expression, Loc::tr('Error in month expression'));
			}
			if (!self::computeYear($year)) {
				// bad expression ?
				throw new Data_List_Exception($expression, Loc::tr('Error in year expression'));
			}
			return self::buildMonthYear($month, $year, $min_max, $expression);
		}
		return false;
	}

	//---------------------------------------------------------------------------- applySingleFormula
	/**
	 * Apply a formula that is alone in the expression (eg. not "15/m+1/2016" but only "m+1")
	 *
	 * @param &$expression string|integer formula
	 * @param $min_max     integer @values Range::MAX_RANGE_VALUE, Range::MIN_RANGE_VALUE,
	 *                                     Range::NOT_A_RANGE_VALUE
	 * @param $part        string Date_Time::DAY | Date_Time::MONTH | Date_Time::YEAR
	 *        | Date_Time::HOUR | Date_Time::MINUTE | Date_Time::SECOND
	 * @return string|Func\Range
	 */
	protected static function applySingleFormula($expression, $min_max, $part)
	{
		if (self::computeFormula($expression, $part)) {
			switch ($part) {
				case Date_Time::YEAR:
					$date_begin = date('Y-m-d H:i:s', mktime(0,  0,  0,  1,  1,  $expression));
					$date_end   = date('Y-m-d H:i:s', mktime(23, 59, 59, 12, 31, $expression));
					break;
				case Date_Time::MONTH:
					$date_begin = date('Y-m-d H:i:s', mktime(0, 0, 0,  $expression,   1, self::$currentYear));
					$date_end   = date('Y-m-d H:i:s', mktime(0, 0, -1, $expression+1, 1, self::$currentYear));
					break;
				case Date_Time::DAY:
					$date_begin = date(
						'Y-m-d H:i:s', mktime(0, 0, 0, self::$currentMonth, $expression, self::$currentYear)
					);
					$date_end = date(
						'Y-m-d H:i:s', mktime(23, 59, 59, self::$currentMonth, $expression, self::$currentYear)
					);
					break;
			}
			/** @noinspection PhpUndefinedVariableInspection All possible cases done by switch */
			return self::buildDateOrPeriod($date_begin, $date_end, $min_max);
		}
		return false;
	}

	//--------------------------------------------------------------------------------- applyYearOnly
	/**
	 * Apply if expression is a year
	 *
	 * @param $expression string
	 * @param $min_max    integer @values Range::MAX_RANGE_VALUE, Range::MIN_RANGE_VALUE,
	 *                                    Range::NOT_A_RANGE_VALUE
	 * @return mixed|boolean false
	 * @throws Data_List_Exception
	 */
	protected static function applyYearOnly($expression, $min_max)
	{
		$letters_year = self::getDateLetters(Date_Time::YEAR);
		// no slash and (>3 digit or "y" or "a")
		if (preg_match(
			'/^ \s* ([0-9*?%_]{3,4} | ([' . $letters_year . ']([-+]\d+)?)) \s* $/x', $expression
		)) {
			$year = $expression;
			if (self::computeYear($year)) {
				if (Joker::hasJoker($year)) {
					list($day, $month, $year) = self::padDateParts('__', '__', $year);
					$date = Func::like("$year-$month-$day __:__:__");
				}
				else {
					$date_begin = date('Y-m-d H:i:s', mktime(0,  0,  0,  1,  1,  $year));
					$date_end   = date('Y-m-d H:i:s', mktime(23, 59, 59, 12, 31, $year));
					$date = self::buildDateOrPeriod($date_begin, $date_end, $min_max);
				}
				return $date;
			}
			// bad expression?
			throw new Data_List_Exception($expression, Loc::tr('Error in year expression'));
		}
		return false;
	}

	//----------------------------------------------------------------------------- buildDateOrPeriod
	/**
	 * Builds the correct Dao object for given begin and end date according to what we want
	 *
	 * @param $date_begin string
	 * @param $date_end   string
	 * @param $min_max    integer @values Range::MAX_RANGE_VALUE, Range::MIN_RANGE_VALUE,
	 *                                    Range::NOT_A_RANGE_VALUE
	 * @return Func\Range|string
	 */
	protected static function buildDateOrPeriod($date_begin, $date_end, $min_max)
	{
		if ($min_max == Range::MIN_RANGE_VALUE) {
			$date = $date_begin;
		}
		elseif ($min_max == Range::MAX_RANGE_VALUE) {
			$date = $date_end;
		}
		else {
			$date = Range::buildRange($date_begin, $date_end);
		}
		return $date;
	}

	//--------------------------------------------------------------------------------- buildDayMonth
	/**
	 * Builds the date from computed month and a year
	 *
	 * @param $day     string
	 * @param $month   string
	 * @param $min_max integer @values Range::MAX_RANGE_VALUE, Range::MIN_RANGE_VALUE,
	 *                                 Range::NOT_A_RANGE_VALUE
	 * @param $expr    string
	 * @return Comparison|Func\Range
	 * @throws Data_List_Exception
	 */
	private static function buildDayMonth($day, $month, $min_max, $expr)
	{
		if (!(int)$day && !(int)$month) {
			$date = Func::isNull();
		}
		else {
			$dayHasJoker = Joker::hasJoker($day);
			$monthHasJoker = Joker::hasJoker($month);
			if (!$dayHasJoker && !$monthHasJoker) {
				//none has wildcard
				$date_begin = date('Y-m-d H:i:s', mktime(0, 0, 0, $month, $day, self::$currentYear));
				$date_end = date(
					'Y-m-d H:i:s', mktime(0, 0, -1, $month, (int)$day + 1, self::$currentYear)
				);
				$date = self::buildDateOrPeriod($date_begin, $date_end, $min_max);
			}
			else {
				//at least one has wildcard
				if ($min_max != Range::NOT_A_RANGE_VALUE) {
					//we can not have wildcard on a range value
					throw new Data_List_Exception(
						$expr, Loc::tr('You can not have a wildcard on a range value')
					);
				}
				if (!$monthHasJoker) {
					//day has wildcard, month may be computed
					//try to correct month and year
					$time = mktime(0, 0, 0, $month, 1, self::$currentYear);
					$year = date('Y', $time);
					$month = date('m', $time);
					list($day, $month, $year) = self::padDateParts($day, $month, $year);
					$date = Func::like("$year-$month-$day __:__:__");
				}
				elseif (!$dayHasJoker) {
					//month has wildcard but not day that may be computed.
					//So we should take care if day is <1 or >31 //TODO:what about 30? 29? 28?
					if ($day < 1 || $day > 31) {
						throw new Data_List_Exception(
							$expr, Loc::tr('You can not put a formula on day when month has wildcard')
						);
					}
					list($day, $month) = self::padDateParts($day, $month, 'fooo');
					$date = Func::like(self::$currentYear . "-$month-$day __:__:__");
				}
				else {
					//both day and month have wildcards
					list($day, $month) = self::padDateParts($day, $month, 'fooo');
					$date = Func::like(self::$currentYear . "-$month-$day __:__:__");
				}
			}
		}
		return $date;
	}

	//----------------------------------------------------------------------------- buildDayMonthYear
	/**
	 * Build the date from computed day, month and year
	 *
	 * @param $day        string|integer
	 * @param $month      string|integer
	 * @param $year       string|integer
	 * @param $min_max    integer @values Range::MAX_RANGE_VALUE, Range::MIN_RANGE_VALUE,
	 *                                    Range::NOT_A_RANGE_VALUE
	 * @param $expression string
	 * @return Comparison|Func\Range
	 * @throws Data_List_Exception
	 */
	private static function buildDayMonthYear($day, $month, $year, $min_max, $expression)
	{
		if (!(int)$day && !(int)$month && !(int)$year) {
			$date = Func::isNull();
		}
		else {
			$day_has_joker = Joker::hasJoker($day);
			$month_has_joker = Joker::hasJoker($month);
			$year_has_joker = Joker::hasJoker($year);
			if (!$day_has_joker && !$month_has_joker && !$year_has_joker) {
				// none has wildcard
				$date_begin = date('Y-m-d H:i:s', mktime(0, 0, 0, $month, $day, $year));
				$date_end = date('Y-m-d H:i:s', mktime(0, 0, -1, $month, $day + 1, $year));
				$date = self::buildDateOrPeriod($date_begin, $date_end, $min_max);
			}
			else {
				// at least one has wildcard
				if ($min_max != Range::NOT_A_RANGE_VALUE) {
					//we can not have wildcard on a range value
					throw new Data_List_Exception(
						$expression, Loc::tr('You can not have a wildcard on a range value')
					);
				}
				if (
					// 000: all have wildcards
					($day_has_joker && $month_has_joker && $year_has_joker)
					// 001: day has wildcard, month has wildcard, year may be computed
					|| ($day_has_joker && $month_has_joker && !$year_has_joker)
				) {
					// no need to correct anything!
				}
				if (
					// 010: day has wildcard, month may be computed, year has wildcard
					($day_has_joker && !$month_has_joker && $year_has_joker)
				) {
					if ($month < 1 || $month > 12) {
						throw new Data_List_Exception(
							$expression, Loc::tr('You can not put a formula on month when year has wildcard')
						);
					}
				}
				if (
					// 011: day has wildcard, month may be computed, year may be computed
					($day_has_joker && !$month_has_joker && !$year_has_joker)
				) {
					// try to correct month and year
					$time = mktime(0, 0, 0, $month, 1, $year);
					$year = date('Y', $time);
					$month = date('m', $time);
				}
				if (
					// 100: day may be computed, month has wildcard, year has wildcard
					(!$day_has_joker && $month_has_joker && $year_has_joker)
					// 101: day may be computed, month has wildcard, year may be computed
					|| (!$day_has_joker && $month_has_joker && !$year_has_joker)
				) {
					//So we should take care if day is <1 or >31 //TODO:what about 30? 29? 28?
					if ($day < 1 || $day > 31) {
						throw new Data_List_Exception(
							$expression, Loc::tr('You can not put a formula on day when month has wildcard')
						);
					}
				}
				list($day, $month, $year) = self::padDateParts($day, $month, $year);
				$date = Func::like("$year-$month-$day __:__:__");
			}
		}
		return $date;
	}

	//-------------------------------------------------------------------------------- buildMonthYear
	/**
	 * Build the date from computed month and a yea
	 *
	 * @param $month      string|integer
	 * @param $year       string|integer
	 * @param $min_max    integer @values Range::MAX_RANGE_VALUE, Range::MIN_RANGE_VALUE,
	 *                                    Range::NOT_A_RANGE_VALUE
	 * @param $expression string
	 * @return Comparison|Func\Range
	 * @throws Data_List_Exception
	 */
	private static function buildMonthYear($month, $year, $min_max, $expression)
	{
		if (!(int)$month && !(int)$year) {
			$date = Func::isNull();
		}
		else {
			$month_has_joker = Joker::hasJoker($month);
			$year_has_joker = Joker::hasJoker($year);
			if (!$month_has_joker && !$year_has_joker) {
				$date_begin = date('Y-m-d H:i:s', mktime(0, 0, 0, $month, 1, $year));
				$date_end = date('Y-m-d H:i:s', mktime(0, 0, -1, $month + 1, 1, $year));
				$date = self::buildDateOrPeriod($date_begin, $date_end, $min_max);
			}
			elseif (!$year_has_joker) {
				// month has wildcard, year may be computed
				list($day, $month, $year) = self::padDateParts('__', $month, $year);
				$date = Func::like("$year-$month-$day __:__:__");
			}
			elseif (!$month_has_joker) {
				// year has wildcard but not month that may be computed.
				// So we should take care if month is <1 or >12
				if ($month < 1 || $month > 12) {
					throw new Data_List_Exception(
						$expression, Loc::tr('You can not put a formula on month when year has wildcard')
					);
				}
				list($day, $month, $year) = self::padDateParts('__', $month, $year);
				$date = Func::like("$year-$month-$day __:__:__");
			}
			else {
				// both year and month have wildcards
				list($day, $month, $year) = self::padDateParts('__', $month, $year);
				$date = Func::like("$year-$month-$day __:__:__");
			}
		}
		return $date;
	}

	//------------------------------------------------------------------------- checkDateWildcardExpr
	/**
	 * Check an expression (part of a datetime) contains wildcards and correct it, if necessary
	 * @param &$expression string
	 * @param $part string Date_Time::DAY | Date_Time::MONTH | Date_Time::YEAR | Date_Time::HOUR
	 *        | Date_Time::MINUTE | Date_Time::SECOND
	 * @return boolean
	 */
	public static function checkDateWildcardExpr(&$expression, $part)
	{
		$expression = str_replace(['*', '?'], ['%', '_'], $expression);
		$nchar = ($part == Date_Time::YEAR ? 4 : 2);
		if ($c = preg_match_all("/^[0-9_%]{1,$nchar}$/", $expression)) {
			self::correctDateWildcardExpr($expression, $part);
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
		return is_numeric($expression) && (string)((int)$expression) == $expression;
	}

	//------------------------------------------------------------------------------------ computeDay
	/**
	 * Compute a day expression to get a string suitable to build a Date
	 *
	 * @param $expression string numeric or with widlcard or formula d+1 | d+3 | d-2 | j+1 | j+3
	 *        | j-2... returns computed if any
	 * @return boolean
	 */
	protected static function computeDay(&$expression)
	{
		$expression = trim($expression);
		// numeric expr
		if (self::checkNumericExpr($expression)) {
			return true;
		}
		// expression with wildcards
		if (self::checkDateWildcardExpr($expression, Date_Time::DAY)) {
			return true;
		}
		// expression with formula
		if (self::computeFormula($expression, Date_Time::DAY)) {
			return true;
		}
		return false;
	}

	//-------------------------------------------------------------------------------- computeFormula
	/**
	 * Compile a formula and compute value for a part of date
	 *
	 * @param &$expression string formula
	 * @param $part        string Date_Time::DAY | Date_Time::MONTH | Date_Time::YEAR
	 *        | Date_Time::HOUR | Date_Time::MINUTE | Date_Time::SECOND
	 * @return boolean true if formula found
	 */
	protected static function computeFormula(&$expression, $part)
	{
		$pp = '[' . self::getDateLetters($part) . ']';
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
			$value = (int)(self::$currentDateTime->format($f[$part]));
			if (isset($matches['sign']) && isset($matches['operand'])) {
				$sign = $matches['sign'];
				$operand = (int)($matches['operand']);
				$expression = (string)($sign == '+' ? $value + $operand : $value - $operand);
			}
			else {
				$expression = $value;
			}
			return true;
		}
		return false;
	}

	//---------------------------------------------------------------------------------- computeMonth
	/**
	 * Compute a month expression to get a string suitable to build a Date
	 *
	 * @param $expression string numeric or with wildcard or formula m+1 | m+3 | m-2...
	 *        returns computed if any
	 * @return boolean
	 */
	protected static function computeMonth(&$expression)
	{
		$expression = trim($expression);
		// numeric expression
		if (self::checkNumericExpr($expression)) {
			return true;
		}
		// expression with wildcards
		if (self::checkDateWildcardExpr($expression, Date_Time::MONTH)) {
			return true;
		}
		// expression with formula
		if (self::computeFormula($expression, Date_Time::MONTH)) {
			return true;
		}
		return false;
	}

	//----------------------------------------------------------------------------------- computeYear
	/**
	 * Compute a year expression to get a string suitable to build a Date
	 *
	 * @param $expression string numeric or with wildcard or formula like y+1 | y+3 | a+1 | a+3...
	 *        returns computed if any
	 * @return boolean
	 */
	protected static function computeYear(&$expression)
	{
		$expression = trim($expression);
		// numeric expression
		if (self::checkNumericExpr($expression)) {
			return true;
		}
		// expression with wildcards
		if (self::checkDateWildcardExpr($expression, Date_Time::YEAR)) {
			return true;
		}
		// expression with formula
		if (self::computeFormula($expression, Date_Time::YEAR)) {
			return true;
		}
		return false;
	}

	//----------------------------------------------------------------------- correctDateWildcardExpr
	/**
	 * Correct a date expression containing SQL wildcard in order to build a Date string
	 *
	 * @param &$expression string
	 * @param $part        string Date_Time::DAY | Date_Time::MONTH | Date_Time::YEAR
	 *        | Date_Time::HOUR | Date_Time::MINUTE | Date_Time::SECOND
	 */
	protected static function correctDateWildcardExpr(&$expression, $part)
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
		if ($part != Date_Time::YEAR) {
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

	//----------------------------------------------------------------------------- getDateSubPattern
	/**
	 * Gets the PCRE Pattern of a date that may contain formula in its part
	 *
	 * e.g 1/m-1 | 1/m+2/y-1 | d-7 | ...
	 * Note: this is not the complete pattern, you should surround by delimiters
	 * and add whatever else you want
	 *
	 * @return string
	 */
	public static function getDateSubPattern()
	{
		static $pattern = false;
		if (!$pattern) {
			$letters = self::getDateLetters(Date_Time::YEAR)
				. self::getDateLetters(Date_Time::MONTH)
				. self::getDateLetters(Date_Time::DAY);
			$pattern = '(?:(?:[0-9*?%_]{1,4} | [' . $letters . '](?:[-+]\d+)?) [\/]){0,2}'
				. SP . '(?:[0-9*?%_]{1,4} | [' . $letters . '](?:[-+]\d+)?)';
		}
		return $pattern;
	}

	//-------------------------------------------------------------------------------- getDateLetters
	/**
	 * Gets the letters that can be used in formula for a part of a date
	 *
	 * @param $part string Date_Time::DAY | Date_Time::MONTH | Date_Time::YEAR
	 * @return string
	 */
	private static function getDateLetters($part)
	{
		static $letters;
		if (!isset($letters)) {
			$letters = explode('|', Loc::tr('d|m|y') . '|' . Loc::tr('h|m|s'));
			$ipUp = function($letter) { return isset($letter) ? ($letter . strtoupper($letter)) : ''; };
			$letters = [
				Date_Time::DAY     => 'dD' . $ipUp($letters[0] != 'd' ? $letters[0] : null),
				Date_Time::MONTH   => 'mM' . $ipUp($letters[1] != 'm' ? $letters[1] : null),
				Date_Time::YEAR    => 'yY' . $ipUp($letters[2] != 'y' ? $letters[2] : null),
				Date_Time::HOUR    => 'hH' . $ipUp($letters[3] != 'h' ? $letters[3] : null),
				Date_Time::MINUTE  => 'iI',
				Date_Time::SECOND  => 'sS' . $ipUp($letters[5] != 's' ? $letters[5] : null)
			];
		}
		return $letters[$part];
	}

	//------------------------------------------------------------------------- getDateWordsToCompare
	/**
	 * get the words to compare with a date word in search expression
	 *
	 * @param $part string
	 * @return array
	 */
	private static function getDateWordsToCompare($part)
	{
		static $all_words_references = [
			Date_Time::DAY   => ['current day', 'today'],
			Date_Time::MONTH => ['current month'],
			Date_Time::YEAR  => ['current year'],
			'yesterday'      => ['yesterday']
		];
		$words_references = $all_words_references[$part];
		$words_localized  = [];
		foreach($words_references as $word) {
			$words_localized[] = Loc::tr($word);
		}
		return Words::getCompressedWords(array_merge($words_references, $words_localized));
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
		self::$currentDateTime = $date;
		self::$currentYear     = self::$currentDateTime->format('Y');
		self::$currentMonth    = self::$currentDateTime->format('m');
		self::$currentDay      = self::$currentDateTime->format('d');
		self::$currentHour     = self::$currentDateTime->format('H');
		self::$currentMinutes  = self::$currentDateTime->format('i');
		self::$currentSeconds  = self::$currentDateTime->format('s');
	}

	//-------------------------------------------------------------------------- isASingleDateFormula
	/**
	 * Check if expression if a single date containing a formula
	 *
	 * @param $expression string
	 * @return boolean
	 */
	public static function isASingleDateFormula($expression)
	{
		// we check if $expr is a single date containing formula
		// but it may be a range with 2 dates containing formula, what should return false
		// so the use of /^ ... $/
		$pattern = "/^ \\s* " . self::getDateSubPattern() . " \\s* $/x";
		$is = preg_match($pattern, $expression, $matches) ? true	: false;
		return $is;
	}

	//---------------------------------------------------------------------------------- padDateParts
	/**
	 * Pad the date parts to have left leading 0
	 *
	 * @param $day     string|integer
	 * @param $month   string|integer
	 * @param $year    string|integer
	 * @return array
	 */
	private static function padDateParts($day, $month, $year)
	{
		$day   = str_pad($day,   2, '0', STR_PAD_LEFT);
		$month = str_pad($month, 2, '0', STR_PAD_LEFT);
		$year  = str_pad($year,  2, '0', STR_PAD_LEFT);
		return [$day, $month, $year];
	}

}
