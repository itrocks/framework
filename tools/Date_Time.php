<?php
namespace ITRocks\Framework\Tools;

use DateInterval;
use DateTime;
use DateTimeZone;
use Exception;

/**
 * This class extends php's DateTime class : you should use this to be ITRocks compatible
 */
class Date_Time extends DateTime implements Can_Be_Empty, Stringable
{

	//------------------------------------------------------------------------------------------- DAY
	const DAY = 'day';

	//--------------------------------------------------------------------------------- DAYS_IN_MONTH
	const DAYS_IN_MONTH = 't';

	//---------------------------------------------------------------------------------- DAY_OF_MONTH
	const DAY_OF_MONTH = 'd';

	//--------------------------------------------------------------------- DAY_OF_MONTH_WITHOUT_ZERO
	const DAY_OF_MONTH_WITHOUT_ZERO = 'j';

	//----------------------------------------------------------------------------------- DAY_OF_WEEK
	const DAY_OF_WEEK = 'w';

	//------------------------------------------------------------------------------- DAY_OF_WEEK_ISO
	const DAY_OF_WEEK_ISO = 'N';

	//----------------------------------------------------------------------------------- DAY_OF_YEAR
	const DAY_OF_YEAR = 's';

	//------------------------------------------------------------------------------------------ HOUR
	const HOUR = 'hour';

	//---------------------------------------------------------------------------------------- MINUTE
	const MINUTE = 'minute';

	//----------------------------------------------------------------------------------------- MONTH
	const MONTH = 'month';

	//------------------------------------------------------------------------------------------- NOW
	const NOW = 'now';

	//---------------------------------------------------------------------------------------- SECOND
	const SECOND = 'second';

	//------------------------------------------------------------------------------------------ WEEK
	const WEEK = 'week';

	//------------------------------------------------------------------------------------------ YEAR
	const YEAR = 'year';

	//------------------------------------------------------------------------------------- $max_date
	/**
	 * The max date
	 *
	 * @var string
	 */
	private static $max_date = '2999-12-31 00:00:00';

	//------------------------------------------------------------------------------------- $min_date
	/**
	 * The min date
	 *
	 * @var string
	 */
	private static $min_date = '0000-00-00 00:00:00';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Constructor
	 *
	 * @param $time     string|integer|null current time in string or timestamp format
	 *                  If null, current time on timezone will be used to initialize
	 * @param $timezone DateTimeZone
	 */
	public function __construct($time = self::NOW, DateTimeZone $timezone = null)
	{
		if ($time instanceof DateTime) {
			$time = $time->format('Y-m-d H:i:s');
		}
		if (is_integer($time)) {
			$time = date('Y-m-d H:i:s', $time);
		}
		parent::__construct($time, $timezone);
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->toISO(false);
	}

	//------------------------------------------------------------------------------------------- add
	/**
	 * Increments a date for a given unit
	 *
	 * @param $quantity integer|DateInterval
	 * @param $unit     string any of the Date_Time duration unit constants
	 * @return Date_Time
	 */
	public function add(
		/** @noinspection PhpSignatureMismatchDuringInheritanceInspection $quantity + integer */
		$quantity, $unit = Date_Time::DAY
	) {
		if ($quantity instanceof DateInterval) {
			parent::add($quantity);
		}
		elseif (is_numeric($quantity)) {
			if ($quantity < 0) {
				$quantity = -$quantity;
				$invert = true;
			}
			else {
				$invert = false;
			}
			switch ($unit) {
				case Date_Time::HOUR:   $interval = 'PT' . $quantity . 'H'; break;
				case Date_Time::MINUTE: $interval = 'PT' . $quantity . 'M'; break;
				case Date_Time::SECOND: $interval = 'PT' . $quantity . 'S'; break;
				case Date_Time::DAY:    $interval = 'P'  . $quantity . 'D'; break;
				case Date_Time::WEEK:   $interval = 'P'  . ($quantity * 7) . 'D'; break;
				case Date_Time::MONTH:  $interval = 'P'  . $quantity . 'M'; break;
				case Date_Time::YEAR:   $interval = 'P'  . $quantity . 'Y'; break;
			}
			if (isset($interval)) {
				$interval = new DateInterval($interval);
				$interval->invert = $invert;
				parent::add($interval);
			}
		}
		return $this;
	}

	//------------------------------------------------------------------------------ createFromFormat
	/**
	 * @param $format   string
	 * @param $time     string
	 * @param $timezone DateTimeZone
	 * @return Date_Time
	 */
	public static function createFromFormat($format, $time, $timezone = null)
	{
		$dateTime = $timezone
			? parent::createFromFormat($format, $time, $timezone)
			: parent::createFromFormat($format, $time);
		return $timezone
			? new Date_Time($dateTime->format('Y-m-d H:i:s'), $timezone)
			: new Date_Time($dateTime->format('Y-m-d H:i:s'));
	}

	//------------------------------------------------------------------------------------ dayOfMonth
	/**
	 * Returns the number of the day in the month
	 *
	 * @param $leading_zero boolean return leading zero (eg '01') if true, else not (eg 1)
	 * @return string
	 */
	public function dayOfMonth($leading_zero = true)
	{
		return $this->format($leading_zero ? self::DAY_OF_MONTH : self::DAY_OF_MONTH_WITHOUT_ZERO);
	}

	//------------------------------------------------------------------------------------- dayOfWeek
	/**
	 * Returns the number of the day in the week, 1 (monday) through 7 (sunday)
	 *
	 * @param $iso_8601 boolean if set to false, sunday will return 0 instead of 7
	 * @return string
	 */
	public function dayOfWeek($iso_8601 = true)
	{
		return $this->format($iso_8601 ? self::DAY_OF_WEEK_ISO : self::DAY_OF_WEEK);
	}

	//------------------------------------------------------------------------------------- dayOfYear
	/**
	 * Returns the number of the day in the year, starting from 0
	 *
	 * @return integer
	 */
	public function dayOfYear()
	{
		return $this->format(self::DAY_OF_YEAR);
	}

	//----------------------------------------------------------------------------------- daysInMonth
	/**
	 * Returns the number of days in the given month
	 *
	 * @return integer
	 */
	public function daysInMonth()
	{
		return $this->format(self::DAYS_IN_MONTH);
	}

	//------------------------------------------------------------------------------------------ diff
	/**
	 * @param $datetime2 Date_Time
	 * @param $absolute  boolean
	 * @return Date_Interval|false
	 */
	public function diff($datetime2, $absolute = false)
	{
		$parent_diff = parent::diff($datetime2, $absolute);
		try {
			$interval = new Date_Interval($parent_diff->format('P%yY%mM%dDT%hH%iM%sS'));
		}
		// DateInterval does not support kind of P0Y0M7DT-1H44M37S it generated itself with format() !
		// So we do it manually if there is a problem
		catch (Exception $exception) {
			$interval = Date_Interval::createFromDuration(
				$absolute
					? abs($datetime2->getTimestamp() - $this->getTimestamp())
					: ($datetime2->getTimestamp() - $this->getTimestamp())
			);
		}
		$interval->invert = $parent_diff->invert;
		return $interval;
	}

	//-------------------------------------------------------------------------------------- earliest
	/**
	 * Returns the earliest (ie smaller, older) date from the main object and a list of dates
	 *
	 * @param $date Date_Time date-times ...
	 * @return Date_Time
	 */
	public function earliest(Date_Time $date)
	{
		$earliest = $this;
		foreach (func_get_args() as $date) {
			/** @var $date Date_Time */
			if ($date->isBefore($earliest)) {
				$earliest = $date;
			}
		}
		return $earliest;
	}

	//---------------------------------------------------------------------------------------- format
	/**
	 * @param $format string
	 * @return string|integer
	 */
	public function format($format)
	{
		return parent::format($format);
	}

	//--------------------------------------------------------------------------------------- fromISO
	/**
	 * Create a date from an ISO string
	 *
	 * The ISO date can be incomplete : eg '2016-07'. Then the created date will autocomplete :
	 * - with a minimal date-time if $max is false (default) : eg '2016-07-01 00:00:00'
	 * - with a maximal date-time if max is true : eg '2016-07-31 23:59:59'
	 *
	 * @param $date string
	 * @param $max  boolean
	 * @return Date_Time
	 */
	public static function fromISO($date, $max = false)
	{
		return (!empty($date) && (substr($date, 0, 4) !== '0000'))
			? new Date_Time(
				(strlen($date) >= 19)
					? $date
					: ($date . substr(($max ? self::$max_date : '2000-01-01 00:00:00'), strlen($date)))
			)
			: new Date_Time($max ? self::$max_date : self::$min_date);
	}

	//------------------------------------------------------------------------------------ fromString
	/**
	 * @param $string string
	 * @return self
	 */
	public static function fromString($string)
	{
		return static::fromISO($string);
	}

	//-------------------------------------------------------------------------------------------- is
	/**
	 * @param $begin_date Date_Time|string|null
	 * @return boolean
	 */
	public function is($begin_date)
	{
		return $this->toISO(false) === strval($begin_date);
	}

	//--------------------------------------------------------------------------------------- isAfter
	/**
	 * Returns true if date time is strictly after another date time
	 *
	 * If the other date time is null, then it is considered :
	 * - as the littlest possible date if $null_is_late is false : isAfter() will return true
	 * - as the highest possible date if $null_is_late is true : isAfter() will return false
	 *
	 * @param $date_time    Date_Time|string|null
	 * @param $null_is_late boolean
	 * @return boolean
	 */
	public function isAfter($date_time, $null_is_late = false)
	{
		return isset($date_time) ? ($this->toISO(false) > strval($date_time)) : !$null_is_late;
	}

	//-------------------------------------------------------------------------------- isAfterOrEqual
	/**
	 * Returns true if date time is after or equal another date time
	 *
	 * If the other date time is null, then it is considered :
	 * - as the littlest possible date if $null_is_late is false : isAfter() will return true
	 * - as the highest possible date if $null_is_late is true : isAfter() will return false
	 *
	 * @param $date_time    Date_Time|string|null
	 * @param $null_is_late boolean
	 * @return boolean
	 */
	public function isAfterOrEqual($date_time, $null_is_late = false)
	{
		return isset($date_time) ? ($this->toISO(false) >= strval($date_time)) : !$null_is_late;
	}

	//-------------------------------------------------------------------------------------- isBefore
	/**
	 * Returns true if date time is strictly before another date time
	 *
	 * If the other date time is null, then it is considered :
	 * - as the littlest possible date if $null_is_late is false : isAfter() will return false
	 * - as the highest possible date if $null_is_late is true : isAfter() will return true
	 *
	 * @param $date_time    Date_Time|string|null
	 * @param $null_is_late boolean
	 * @return boolean
	 */
	public function isBefore($date_time, $null_is_late = false)
	{
		return isset($date_time) ? ($this->toISO(false) < strval($date_time)) : $null_is_late;
	}

	//------------------------------------------------------------------------------- isBeforeOrEqual
	/**
	 * Returns true if date time is before or equal another date time
	 *
	 * If the other date time is null, then it is considered :
	 * - as the littlest possible date if $null_is_late is false : isAfter() will return false
	 * - as the highest possible date if $null_is_late is true : isAfter() will return true
	 *
	 * @param $date_time    Date_Time|string|null
	 * @param $null_is_late boolean
	 * @return boolean
	 */
	public function isBeforeOrEqual($date_time, $null_is_late = false)
	{
		return isset($date_time) ? ($this->toISO(false) <= strval($date_time)) : $null_is_late;
	}

	//--------------------------------------------------------------------------------------- isEmpty
	/**
	 * Returns true if date is empty (equals to the min() or the max() date)
	 *
	 * @return boolean
	 */
	public function isEmpty()
	{
		return $this->isMin() || $this->isMax();
	}

	//----------------------------------------------------------------------------------------- isMax
	/**
	 * Returns true if date is equals to the max() date
	 *
	 * @return boolean
	 */
	public function isMax()
	{
		return ($this->toISO(false) >= self::$max_date);
	}

	//----------------------------------------------------------------------------------------- isMin
	/**
	 * Returns true if date is equals to the min() date
	 *
	 * @return boolean
	 */
	public function isMin()
	{
		return ($this->toISO(false) <= self::$min_date);
	}

	//-------------------------------------------------------------------------------- lastDayOfMonth
	/**
	 * Returns last day of the month (goes to the end of the month)
	 *
	 * @example 'YYYY-MM-DD HH:II:SS' -> 'YYYY-MM-31 23:59:59'
	 * @return Date_Time
	 */
	public function lastDayOfMonth()
	{
		if ($this->isMin()) {
			return static::min();
		}
		return new Date_Time($this->format('Y-m-t 23:59:59'));
	}

	//---------------------------------------------------------------------------------------- latest
	/**
	 * Returns the latest (ie bigger, greater) date from the main object and a list of dates
	 *
	 * @param $date Date_Time date-times ...
	 * @return Date_Time
	 */
	public function latest(Date_Time $date)
	{
		$latest = $this;
		foreach (func_get_args() as $date) {
			/** @var $date Date_Time */
			if ($date->isAfter($latest)) {
				$latest = $date;
			}
		}
		return $latest;
	}

	//------------------------------------------------------------------------------------------- max
	/**
	 * Returns a maximal date time, far into the future considered as a date that non is after
	 *
	 * @return Date_Time
	 */
	public static function max()
	{
		return new Date_Time(self::$max_date);
	}

	//------------------------------------------------------------------------------------------- min
	/**
	 * Returns a minimal date time, far into the past considered as a date that none is before
	 *
	 * @return Date_Time
	 */
	public static function min()
	{
		return new Date_Time(self::$min_date);
	}

	//------------------------------------------------------------------------------------------- now
	/**
	 * Returns current date-time
	 *
	 * @return Date_Time
	 */
	public static function now()
	{
		return new Date_Time();
	}

	//------------------------------------------------------------------------------------------- sub
	/**
	 * Increments a date for a given unit
	 *
	 * @param $quantity integer|DateInterval
	 * @param $unit     string any of the Date_Time duration unit constants
	 * @return Date_Time
	 */
	public function sub(
		/** @noinspection PhpSignatureMismatchDuringInheritanceInspection $quantity + integer */
		$quantity, $unit = Date_Time::DAY
	) {
		($quantity instanceof DateInterval)
			? parent::sub($quantity)
			: $this->add(-$quantity, $unit);
		return $this;
	}

	//----------------------------------------------------------------------------------------- toISO
	/**
	 * @param $empty_min_max boolean If true, returns an empty string for zero or max dates
	 * @return string
	 */
	public function toISO($empty_min_max = true)
	{
		$format = max($this->format('Y-m-d H:i:s'), self::$min_date);
		return ($empty_min_max && (($format <= self::$min_date) || ($format >= self::$max_date)))
			? '' : $format;
	}

	//--------------------------------------------------------------------------------------- toMonth
	/**
	 * Returns a Date_Time for the month (goes to the beginning of the month)
	 *
	 * @example 'YYYY-MM-DD HH:II:SS' -> 'YYYY-MM-01 00:00:00'
	 * @return Date_Time
	 */
	public function toMonth()
	{
		if ($this->isMin()) {
			return new Date_Time($this);
		}
		return new Date_Time($this->format('Y-m'));
	}

	//----------------------------------------------------------------------------------------- today
	/**
	 * Returns current date, with an empty time (00:00:00)
	 *
	 * @return Date_Time
	 */
	public static function today()
	{
		return new Date_Time(date('Y-m-d 00:00:00'));
	}

}
