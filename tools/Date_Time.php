<?php
namespace ITRocks\Framework\Tools;

use DateInterval;
use DateTime;
use DateTimeZone;
use Exception;

/**
 * This class extends PHP DateTime class : you should use this to be ITRocks compatible
 */
class Date_Time extends DateTime implements Can_Be_Empty, Stringable
{

	//------------------------------------------------------------------------------------------- DAY
	const DAY = 'day';

	//-------------------------------------------------------------------------------------- DAY_NAME
	const DAY_NAME = 'l';

	//-------------------------------------------------------------------------------- DAY_NAME_SHORT
	const DAY_NAME_SHORT = 'D';

	//---------------------------------------------------------------------------------- DAY_OF_MONTH
	const DAY_OF_MONTH = 'd';

	//--------------------------------------------------------------------- DAY_OF_MONTH_WITHOUT_ZERO
	const DAY_OF_MONTH_WITHOUT_ZERO = 'j';

	//----------------------------------------------------------------------------------- DAY_OF_WEEK
	const DAY_OF_WEEK = 'w';

	//------------------------------------------------------------------------------- DAY_OF_WEEK_ISO
	const DAY_OF_WEEK_ISO = 'N';

	//----------------------------------------------------------------------------------- DAY_OF_YEAR
	const DAY_OF_YEAR = 'z';

	//--------------------------------------------------------------------------------- DAYS_IN_MONTH
	const DAYS_IN_MONTH = 't';

	//------------------------------------------------------------------------------------------ HOUR
	const HOUR = 'hour';

	//---------------------------------------------------------------------------------------- MINUTE
	const MINUTE = 'minute';

	//----------------------------------------------------------------------------------------- MONTH
	const MONTH = 'month';

	//------------------------------------------------------------------------------------ MONTH_NAME
	const MONTH_NAME = 'F';

	//------------------------------------------------------------------------------ MONTH_NAME_SHORT
	const MONTH_NAME_SHORT = 'M';

	//--------------------------------------------------------------------------------- MONTH_OF_YEAR
	const MONTH_OF_YEAR = 'm';

	//-------------------------------------------------------------------- MONTH_OF_YEAR_WITHOUT_ZERO
	const MONTH_OF_YEAR_WITHOUT_ZERO = 'n';

	//------------------------------------------------------------------------------------------- NOW
	const NOW = 'now';

	//---------------------------------------------------------------------------------------- SECOND
	const SECOND = 'second';

	//------------------------------------------------------------------------------------------ WEEK
	const WEEK = 'week';

	//---------------------------------------------------------------------------------- WEEK_OF_YEAR
	const WEEK_OF_YEAR = 'W';

	//------------------------------------------------------------------------------------------ YEAR
	const YEAR = 'year';

	//------------------------------------------------------------------------------------- $max_date
	/**
	 * The max date
	 *
	 * @var string
	 */
	protected static $max_date = '2999-12-31 00:00:00';

	//------------------------------------------------------------------------------------- $min_date
	/**
	 * The min date
	 *
	 * @var string
	 */
	protected static $min_date = '0000-00-00 00:00:00';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Constructor
	 *
	 * @param $time     DateTime|integer|string|null current time in string or timestamp format
	 *                  If null, current time on timezone will be used to initialize
	 * @param $timezone DateTimeZone|null
	 * @throws Exception
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
	public function __toString() : string
	{
		return $this->toISO(false);
	}

	//------------------------------------------------------------------------------------------- add
	/**
	 * Increments a date for a given unit
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $quantity integer|DateInterval
	 * @param $unit     string any of the Date_Time duration unit constants
	 * @return Date_Time
	 */
	public function add(
		/** @noinspection PhpSignatureMismatchDuringInheritanceInspection $quantity + integer */
		$quantity, $unit = self::DAY
	) : Date_Time {
		if ($quantity instanceof DateInterval) {
			parent::add($quantity);
		}
		elseif (is_numeric($quantity)) {
			if ($quantity < 0) {
				$quantity = -$quantity;
				$invert   = true;
			}
			else {
				$invert = false;
			}
			switch ($unit) {
				case self::HOUR:   $interval = 'PT' . $quantity . 'H'; break;
				case self::MINUTE: $interval = 'PT' . $quantity . 'M'; break;
				case self::SECOND: $interval = 'PT' . $quantity . 'S'; break;
				case self::DAY:    $interval = 'P'  . $quantity . 'D'; break;
				case self::WEEK:   $interval = 'P'  . ($quantity * 7) . 'D'; break;
				case self::MONTH:  $interval = 'P'  . $quantity . 'M'; break;
				case self::YEAR:   $interval = 'P'  . $quantity . 'Y'; break;
			}
			if (isset($interval)) {
				/** @noinspection PhpUnhandledExceptionInspection $interval is generated and valid */
				$interval         = new DateInterval($interval);
				$interval->invert = $invert;
				parent::add($interval);
			}
		}
		return $this;
	}

	//--------------------------------------------------------------------------------------- compare
	/**
	 * Dates comparison for sorting
	 *
	 * @param $date Date_Time
	 * @return integer -1 if $this < $date, 1 if $this > $date, 0 if they are equal
	 */
	public function compare(Date_Time $date) : int
	{
		return $this->isBefore($date) ? -1 : ($this->isAfter($date) ? 1 : 0);
	}

	//---------------------------------------------------------------------------------- compareEmpty
	/**
	 * Compare if one date is empty and the other not
	 *
	 * @param $date Date_Time
	 * @return boolean true if different, false if both dates are empty or if both dates are set
	 */
	public function compareEmpty(Date_Time $date) : bool
	{
		return ($this->isEmpty() && !$date->isEmpty()) || ($date->isEmpty() && !$this->isEmpty());
	}

	//------------------------------------------------------------------------------ createFromFormat
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $format   string
	 * @param $time     string
	 * @param $timezone DateTimeZone|null
	 * @return Date_Time
	 */
	public static function createFromFormat(
		$format,
		$time,
		/** @noinspection PhpSignatureMismatchDuringInheritanceInspection PhpStorm */ $timezone = null
	) {
		$date_time = $timezone
			? parent::createFromFormat($format, $time, $timezone)
			: parent::createFromFormat($format, $time);
		/** @noinspection PhpUnhandledExceptionInspection valid constant format used */
		return $timezone
			? new static($date_time->format('Y-m-d H:i:s'), $timezone)
			: new static($date_time->format('Y-m-d H:i:s'));
	}

	//------------------------------------------------------------------------------------------- day
	/**
	 * Returns a new date with only the day of the current date (with an empty time)
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @deprecated use toBeginOf(Date_Time::DAY) or toEndOf instead
	 * @param $end_of_day boolean if true, the time will be 23:59:59 instead of an empty time
	 * @return Date_Time
	 * @see toBeginOf
	 * @see toEndOf
	 */
	public function day($end_of_day = false) : Date_Time
	{
		/** @noinspection PhpUnhandledExceptionInspection valid constant format */
		return new static($this->format('Y-m-d') . ($end_of_day ? ' 23:59:59' : ''));
	}

	//------------------------------------------------------------------------------------ dayOfMonth
	/**
	 * Returns the number of the day in the month
	 *
	 * @param $leading_zero boolean return leading zero (eg '01') if true, else not (eg 1)
	 * @return integer|string integer if $leading_zero is false, string if $leading_zero is true
	 */
	public function dayOfMonth($leading_zero = false)
	{
		$day_of_month = $this->format(
			$leading_zero ? self::DAY_OF_MONTH : self::DAY_OF_MONTH_WITHOUT_ZERO
		);
		return $leading_zero ? $day_of_month : intval($day_of_month);
	}

	//------------------------------------------------------------------------------------- dayOfWeek
	/**
	 * Returns the number of the day in the week, 1 (monday) through 7 (sunday)
	 *
	 * @param $iso_8601 boolean if set to false, sunday will return 0 instead of 7
	 * @return integer
	 */
	public function dayOfWeek($iso_8601 = true) : int
	{
		return $this->format($iso_8601 ? self::DAY_OF_WEEK_ISO : self::DAY_OF_WEEK);
	}

	//------------------------------------------------------------------------------------- dayOfYear
	/**
	 * Returns the number of the day in the year, starting from 0
	 *
	 * @return integer
	 */
	public function dayOfYear() : int
	{
		return $this->format(self::DAY_OF_YEAR);
	}

	//---------------------------------------------------------------------------------------- daysIn
	/**
	 * @param $unit string @values day, month, week, year
	 * @return ?integer
	 */
	public function daysIn(string $unit) : ?int
	{
		switch ($unit) {
			case self::DAY:
				return 1;
			case self::MONTH:
				return $this->format(self::DAYS_IN_MONTH);
			case self::WEEK:
				return 7;
			case self::YEAR:
				return (clone $this)->toEndOf(self::YEAR)->format(self::DAY_OF_YEAR);
		}
		return null;
	}

	//----------------------------------------------------------------------------------- daysInMonth
	/**
	 * Returns the number of days in the given month
	 *
	 * @deprecated daysIn is its generic version
	 * @return integer
	 * @see daysIn
	 */
	public function daysInMonth() : int
	{
		return $this->format(self::DAYS_IN_MONTH);
	}

	//-------------------------------------------------------------------------------------- earliest
	/**
	 * Returns the earliest (ie smaller, older) date from the main object and a list of dates
	 *
	 * @param $date Date_Time date-times ...
	 * @return Date_Time
	 */
	public function earliest(Date_Time $date) : Date_Time
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

	//----------------------------------------------------------------------------------------- empty
	/**
	 * Returns an arbitrary empty date
	 * Alias for Date_Time::min()
	 *
	 * @return static
	 */
	public static function empty() : Date_Time
	{
		return static::min();
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
	 * @throws Exception
	 */
	public static function fromISO(string $date, bool $max = false) : Date_Time
	{
		return (!empty($date) && (substr($date, 0, 4) !== '0000'))
			? new static(
				(strlen($date) >= 19)
					? $date
					: ($date . substr(($max ? self::$max_date : '2000-01-01 00:00:00'), strlen($date)))
			)
			: new static($max ? self::$max_date : self::$min_date);
	}

	//------------------------------------------------------------------------------------ fromString
	/**
	 * @param $string string
	 * @return static
	 * @throws Exception
	 */
	public static function fromString($string) : Date_Time
	{
		return static::fromISO($string);
	}

	//-------------------------------------------------------------------------------------------- is
	/**
	 * @param $date Date_Time|string|null
	 * @return boolean
	 */
	public function is($date) : bool
	{
		return $this->toISO(false) === strval($date);
	}

	//--------------------------------------------------------------------------------------- isAfter
	/**
	 * Returns true if date time is strictly after another date time
	 *
	 * If the given date time is null, then it is considered :
	 * - as the littlest possible date if $null_is_late is false : will return true
	 * - as the highest possible date if $null_is_late is true : will return false
	 *
	 * @param $date_time    Date_Time|string|null
	 * @param $null_is_late boolean
	 * @return boolean
	 */
	public function isAfter($date_time, bool $null_is_late = false) : bool
	{
		return isset($date_time) ? ($this->toISO(false) > strval($date_time)) : !$null_is_late;
	}

	//-------------------------------------------------------------------------------- isAfterOrEqual
	/**
	 * Returns true if date time is after or equal another date time
	 *
	 * If the given date time is null, then it is considered :
	 * - as the littlest possible date if $null_is_late is false : will return true
	 * - as the highest possible date if $null_is_late is true : will return false
	 *
	 * @param $date_time    Date_Time|string|null
	 * @param $null_is_late boolean
	 * @return boolean
	 */
	public function isAfterOrEqual($date_time, bool $null_is_late = false) : bool
	{
		return isset($date_time) ? ($this->toISO(false) >= strval($date_time)) : !$null_is_late;
	}

	//-------------------------------------------------------------------------------------- isBefore
	/**
	 * Returns true if date time is strictly before another date time
	 *
	 * If the given date time is null, then it is considered :
	 * - as the littlest possible date if $null_is_late is false : will return false
	 * - as the highest possible date if $null_is_late is true : will return true
	 *
	 * @param $date_time    Date_Time|string|null
	 * @param $null_is_late boolean
	 * @return boolean
	 */
	public function isBefore($date_time, bool $null_is_late = false) : bool
	{
		return isset($date_time) ? ($this->toISO(false) < strval($date_time)) : $null_is_late;
	}

	//------------------------------------------------------------------------------- isBeforeOrEqual
	/**
	 * Returns true if date time is before or equal another date time
	 *
	 * If the given date time is null, then it is considered :
	 * - as the littlest possible date if $null_is_late is false : will return false
	 * - as the highest possible date if $null_is_late is true : will return true
	 *
	 * @param $date_time    Date_Time|string|null
	 * @param $null_is_late boolean
	 * @return boolean
	 */
	public function isBeforeOrEqual($date_time, bool $null_is_late = false) : bool
	{
		return isset($date_time) ? ($this->toISO(false) <= strval($date_time)) : $null_is_late;
	}

	//--------------------------------------------------------------------------------------- isEmpty
	/**
	 * Returns true if date is empty (equals to the min() or the max() date)
	 *
	 * @return boolean
	 */
	public function isEmpty() : bool
	{
		return $this->isMin() || $this->isMax();
	}

	//--------------------------------------------------------------------------------------- isEndOf
	/**
	 * @param $unit string @values day, hour, minute, month, week, year
	 * @return ?boolean
	 */
	public function isEndOf(string $unit) : ?bool
	{
		switch ($unit) {
			case self::DAY:    return $this->format('H:i:s') === '23:59:59';
			case self::HOUR:   return $this->format('i')     === '59';
			case self::MINUTE: return $this->format('s')     === '59';
			case self::MONTH:  return $this->dayOfMonth()    === $this->daysIn(Date_Time::MONTH);
			case self::WEEK:   return $this->dayOfWeek()     === 7;
			case self::YEAR:   return $this->dayOfYear()     === $this->daysIn(Date_Time::YEAR);
		}
		return null;
	}

	//----------------------------------------------------------------------------------------- isMax
	/**
	 * Returns true if date is equals to the max() date
	 *
	 * @return boolean
	 */
	public function isMax() : bool
	{
		return ($this->toISO(false) >= self::$max_date);
	}

	//----------------------------------------------------------------------------------------- isMin
	/**
	 * Returns true if date is equals to the min() date
	 *
	 * @return boolean
	 */
	public function isMin() : bool
	{
		return ($this->toISO(false) <= self::$min_date);
	}

	//-------------------------------------------------------------------------------- lastDayOfMonth
	/**
	 * Returns last day of the month (goes to the end of the month)
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @deprecated use toEndOf instead
	 * @example 'YYYY-MM-DD HH:II:SS' -> 'YYYY-MM-31 23:59:59'
	 * @return Date_Time
	 * @see toEndOf
	 */
	public function lastDayOfMonth() : Date_Time
	{
		if ($this->isEmpty()) {
			/** @noinspection PhpUnhandledExceptionInspection copy of valid $this */
			return new static($this);
		}
		/** @noinspection PhpUnhandledExceptionInspection valid format */
		return new static($this->format('Y-m-t 23:59:59'));
	}

	//---------------------------------------------------------------------------------------- latest
	/**
	 * Returns the latest (ie bigger, greater) date from the main object and a list of dates
	 *
	 * @param $date Date_Time date-times ...
	 * @return Date_Time
	 */
	public function latest(Date_Time $date) : Date_Time
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
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return Date_Time
	 * @return_constant
	 */
	public static function max() : Date_Time
	{
		/** @noinspection PhpUnhandledExceptionInspection valid constant */
		return new static(self::$max_date);
	}

	//------------------------------------------------------------------------------------------- min
	/**
	 * Returns a minimal date time, far into the past considered as a date that none is before
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return Date_Time
	 * @return_constant
	 */
	public static function min() : Date_Time
	{
		/** @noinspection PhpUnhandledExceptionInspection valid constant */
		return new static(self::$min_date);
	}

	//----------------------------------------------------------------------------------------- month
	/**
	 * Returns a Date_Time for the month (goes to the beginning of the month)
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @deprecated use toBeginOf(Date_Time::MONTH) instead
	 * @example 'YYYY-MM-DD HH:II:SS' -> 'YYYY-MM-01 00:00:00'
	 * @return Date_Time
	 * @see toBeginOf
	 */
	public function month() : Date_Time
	{
		if ($this->isEmpty()) {
			/** @noinspection PhpUnhandledExceptionInspection valid copy of $this */
			return new static($this);
		}
		/** @noinspection PhpUnhandledExceptionInspection valid format */
		return new static($this->format('Y-m'));
	}

	//------------------------------------------------------------------------------------------- now
	/**
	 * Returns current date-time
	 *
	 * @return Date_Time
	 */
	public static function now() : Date_Time
	{
		return new static();
	}

	//------------------------------------------------------------------------------------- nowMinute
	/**
	 * Returns current date-time, without initializing seconds
	 *
	 * @return Date_Time
	 */
	public static function nowMinute() : Date_Time
	{
		$date_time = new static();
		$date_time->setTime($date_time->format('H'), $date_time->format('i'));
		return $date_time;
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
		$quantity, $unit = self::DAY
	) : Date_Time {
		($quantity instanceof DateInterval)
			? parent::sub($quantity)
			: $this->add(-$quantity, $unit);
		return $this;
	}

	//------------------------------------------------------------------------------------- toBeginOf
	/**
	 * Returns a new date of the beginning of the $unit
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @example 'YYYY-MM-DD HH:II:SS'(Date_Time::MINUTE) => 'YYYY-MM-DD HH:II:00'
	 * @example 'YYYY-MM-DD HH:II:SS'(Date_Time::HOUR)   => 'YYYY-MM-DD HH:00:00'
	 * @example 'YYYY-MM-DD HH:II:SS'(Date_Time::DAY)    => 'YYYY-MM-DD 00:00:00'
	 * @example 'YYYY-MM-DD HH:II:SS'(Date_Time::MONTH)  => 'YYYY-MM-01 00:00:00'
	 * @example 'YYYY-MM-DD HH:II:SS'(Date_Time::YEAR)   => 'YYYY-01-01 00:00:00'
	 * @param $unit string @values day, hour, month, minute, year
	 * @return Date_Time
	 */
	public function toBeginOf(string $unit) : Date_Time
	{
		if ($this->isEmpty()) {
			/** @noinspection PhpUnhandledExceptionInspection valid copy of $this */
			return new static($this);
		}
		switch ($unit) {
			case self::MINUTE:
				$format = 'Y-m-d H:i:00';
				break;
			case self::HOUR:
				$format = 'Y-m-d H:00:00';
				break;
			case self::DAY:
				$format = 'Y-m-d 00:00:00';
				break;
			case self::WEEK:
				/** @noinspection PhpUnhandledExceptionInspection valid $this and constant format */
				return (new static($this->format('Y-m-d 00:00:00')))
					->sub($this->format(self::DAY_OF_WEEK_ISO) - 1);
			case self::MONTH:
				$format = 'Y-m-01 00:00:00';
				break;
			case self::YEAR:
				$format = 'Y-01-01 00:00:00';
				break;
			// invalid value for $unit : a new Date_Time with the same time
			default:
				$format = 'Y-m-d H:i:s';
		}
		/** @noinspection PhpUnhandledExceptionInspection valid $this and constant format */
		return new static($this->format($format));
	}

	//--------------------------------------------------------------------------------- toBeginningOf
	/**
	 * Returns a new date of the beginning of the $unit
	 *
	 * @deprecated toBeginOf is shorter
	 * @example 'YYYY-MM-DD HH:II:SS'(Date_Time::MINUTE) => 'YYYY-MM-DD HH:II:00'
	 * @example 'YYYY-MM-DD HH:II:SS'(Date_Time::HOUR)   => 'YYYY-MM-DD HH:00:00'
	 * @example 'YYYY-MM-DD HH:II:SS'(Date_Time::DAY)    => 'YYYY-MM-DD 00:00:00'
	 * @example 'YYYY-MM-DD HH:II:SS'(Date_Time::MONTH)  => 'YYYY-MM-01 00:00:00'
	 * @example 'YYYY-MM-DD HH:II:SS'(Date_Time::YEAR)   => 'YYYY-01-01 00:00:00'
	 * @param $unit string @values day, hour, month, minute, year
	 * @return Date_Time
	 * @see toBeginOf
	 */
	public function toBeginningOf(string $unit) : Date_Time
	{
		return $this->toBeginOf($unit);
	}

	//--------------------------------------------------------------------------------------- toEndOf
	/**
	 * Returns a new date of the end of the $unit
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @example 'YYYY-MM-DD HH:II:SS'(Date_Time::MINUTE) => 'YYYY-MM-DD HH:II:00'
	 * @example 'YYYY-MM-DD HH:II:SS'(Date_Time::HOUR)   => 'YYYY-MM-DD HH:00:00'
	 * @example 'YYYY-MM-DD HH:II:SS'(Date_Time::DAY)    => 'YYYY-MM-DD 00:00:00'
	 * @example 'YYYY-MM-DD HH:II:SS'(Date_Time::MONTH)  => 'YYYY-MM-01 00:00:00'
	 * @example 'YYYY-MM-DD HH:II:SS'(Date_Time::YEAR)   => 'YYYY-01-01 00:00:00'
	 * @param $unit string @values day, hour, minute, month, week, year
	 * @return Date_Time
	 */
	public function toEndOf(string $unit) : Date_Time
	{
		if ($this->isEmpty()) {
			/** @noinspection PhpUnhandledExceptionInspection valid copy of $this */
			return new static($this);
		}
		switch ($unit) {
			case self::DAY:
				$format = 'Y-m-d 23:59:59';
				break;
			case self::HOUR:
				$format = 'Y-m-d H:59:59';
				break;
			case self::MINUTE:
				$format = 'Y-m-d H:i:59';
				break;
			case self::MONTH:
				$format = 'Y-m-t 23:59:59';
				break;
			case self::WEEK:
				/** @noinspection PhpUnhandledExceptionInspection valid copy of $this and constant format */
				return (new static($this->format('Y-m-d 23:59:59')))
					->add(7 - $this->format(self::DAY_OF_WEEK_ISO));
			case self::YEAR:
				$format = 'Y-12-31 23:59:59';
				break;
			// invalid value for $unit : a new Date_Time with the same time
			default:
				$format = 'Y-m-d H:i:s';
		}
		/** @noinspection PhpUnhandledExceptionInspection valid copy of $this and constant format */
		return new static($this->format($format));
	}

	//----------------------------------------------------------------------------------------- toISO
	/**
	 * @param $empty_min_max boolean If true, returns an empty string for zero or max dates
	 * @return string
	 */
	public function toISO(bool $empty_min_max = true) : string
	{
		$format = max($this->format('Y-m-d H:i:s'), self::$min_date);
		return ($empty_min_max && (($format <= self::$min_date) || ($format >= self::$max_date)))
			? '' : $format;
	}

	//-------------------------------------------------------------------------------------- toISODay
	/**
	 * @param $empty_min_max boolean If true, returns an empty string for zero or max dates
	 * @return string
	 */
	public function toISODay(bool $empty_min_max = true) : string
	{
		return substr($this->toISO($empty_min_max), 0, 10);
	}

	//--------------------------------------------------------------------------------------- toMonth
	/**
	 * Returns a Date_Time for the month (goes to the beginning of the month)
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @deprecated Please use month() instead
	 * @example    'YYYY-MM-DD HH:II:SS' -> 'YYYY-MM-01 00:00:00'
	 * @return     Date_Time
	 */
	public function toMonth() : Date_Time
	{
		if ($this->isMin()) {
			/** @noinspection PhpUnhandledExceptionInspection valid copy of $this */
			return new static($this);
		}
		/** @noinspection PhpUnhandledExceptionInspection valid $this and constant format */
		return new static($this->format('Y-m'));
	}

	//---------------------------------------------------------------------------------------- toNext
	/**
	 * @param $what      int|string
	 * @param $what_else string|null
	 * @return Date_Time
	 */
	public function toNext($what, string $what_else = null) : Date_Time
	{
		return $what_else
			? (clone $this)->add($what, $what_else)
			: (is_numeric($what) ? (clone $this)->add($what) : (clone $this)->add(1, $what_else));
	}

	//------------------------------------------------------------------------------------ toPrevious
	/**
	 * @param $what      int|string
	 * @param $what_else string|null
	 * @return Date_Time
	 */
	public function toPrevious($what, string $what_else = null) : Date_Time
	{
		return $what_else
			? (clone $this)->sub($what, $what_else)
			: (is_numeric($what) ? (clone $this)->sub($what) : (clone $this)->sub(1, $what_else));
	}

	//----------------------------------------------------------------------------------------- today
	/**
	 * Returns current date, with an empty time (00:00:00)
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return Date_Time
	 */
	public static function today() : Date_Time
	{
		/** @noinspection PhpUnhandledExceptionInspection valid constant format */
		return new static(date('Y-m-d 00:00:00'));
	}

	//-------------------------------------------------------------------------------------- tomorrow
	/**
	 * Returns tomorrow date, with an empty time (00:00:00).
	 *
	 * @return Date_Time
	 */
	public static function tomorrow() : Date_Time
	{
		return static::today()->add(1);
	}

	//------------------------------------------------------------------------------------- yesterday
	/**
	 * Returns yesterday date, with an empty time (00:00:00).
	 *
	 * @return Date_Time
	 */
	public static function yesterday() : Date_Time
	{
		return static::today()->sub(1);
	}

}
