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

	//------------------------------------------------------------------------------------- Constants
	const DAY = 'day';
	const DAY_NAME = 'l';
	const DAY_NAME_SHORT = 'D';
	const DAY_OF_MONTH = 'd';
	const DAY_OF_MONTH_WITHOUT_ZERO = 'j';
	const DAY_OF_WEEK = 'w';
	const DAY_OF_WEEK_ISO = 'N';
	const DAY_OF_YEAR = 'z';
	const DAYS_IN_MONTH = 't';
	const HOUR = 'hour';
	const MINUTE = 'minute';
	const MONTH = 'month';
	const MONTH_NAME = 'F';
	const MONTH_NAME_SHORT = 'M';
	const MONTH_OF_YEAR = 'm';
	const MONTH_OF_YEAR_WITHOUT_ZERO = 'n';
	const NOW = 'now';
	const SECOND = 'second';
	const WEEK = 'week';
	const WEEK_OF_YEAR = 'W';
	const YEAR = 'year';

	//------------------------------------------------------------------------------------- $max_date
	/** The max date */
	protected static string $max_date = '2999-12-31 00:00:00';

	//------------------------------------------------------------------------------------- $min_date
	/** The min date */
	protected static string $min_date = '0000-00-00 00:00:00';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $time     integer|string|DateTime|null current time in string or timestamp format
	 *                  If null, current time on timezone will be used to initialize
	 * @param $timezone DateTimeZone|null
	 * @throws Exception
	 */
	public function __construct(
		int|string|DateTime|null $time = self::NOW, DateTimeZone $timezone = null
	) {
		if ($time instanceof DateTime) {
			$time = $time->format('Y-m-d H:i:s');
		}
		if (is_integer($time)) {
			$time = date('Y-m-d H:i:s', $time);
		}
		parent::__construct($time, $timezone);
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return $this->toISO(false);
	}

	//------------------------------------------------------------------------------------------- add
	/**
	 * Increments a date for a given unit
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $interval DateInterval|integer
	 * @param $unit     string any of the Date_Time duration unit constants
	 * @return $this
	 */
	public function add(DateInterval|int $interval, string $unit = self::DAY) : static
	{
		if (is_int($interval)) {
			if ($interval < 0) {
				$interval = -$interval;
				$invert   = true;
			}
			else {
				$invert = false;
			}
			switch ($unit) {
				case self::HOUR:   $interval = 'PT' . $interval . 'H'; break;
				case self::MINUTE: $interval = 'PT' . $interval . 'M'; break;
				case self::SECOND: $interval = 'PT' . $interval . 'S'; break;
				case self::DAY:    $interval = 'P'  . $interval . 'D'; break;
				case self::WEEK:   $interval = 'P'  . ($interval * 7) . 'D'; break;
				case self::MONTH:  $interval = 'P'  . $interval . 'M'; break;
				case self::YEAR:   $interval = 'P'  . $interval . 'Y'; break;
			}
			/** @noinspection PhpUnhandledExceptionInspection $interval is generated and valid */
			$interval         = new DateInterval($interval);
			$interval->invert = $invert;
		}
		parent::add($interval);
		return $this;
	}

	//--------------------------------------------------------------------------------------- compare
	/**
	 * Dates comparison for sorting
	 *
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
	 * @return boolean true if different, false if both dates are empty or if both dates are set
	 */
	public function compareEmpty(Date_Time $date) : bool
	{
		return ($this->isEmpty() && !$date->isEmpty()) || ($date->isEmpty() && !$this->isEmpty());
	}

	//------------------------------------------------------------------------------ createFromFormat
	public static function createFromFormat(
		string $format, string $datetime, DateTimeZone $timezone = null
	) : static|false
	{
		$date_time = $timezone
			? parent::createFromFormat($format, $datetime, $timezone)
			: parent::createFromFormat($format, $datetime);
		/** @noinspection PhpUnhandledExceptionInspection valid constant format used */
		return $timezone
			? new static($date_time->format('Y-m-d H:i:s'), $timezone)
			: new static($date_time->format('Y-m-d H:i:s'));
	}

	//----------------------------------------------------------------------------------- currentYear
	public static function currentYear() : int
	{
		return date('Y');
	}

	//------------------------------------------------------------------------------------------- day
	/**
	 * Returns a new date with only the day of the current date (with an empty time)
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $end_of_day boolean if true, the time will be 23:59:59 instead of an empty time
	 * @return static
	 * @see toBeginOf
	 * @see toEndOf
	 */
	public function day(bool $end_of_day = false) : static
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
	public function dayOfMonth(bool $leading_zero = false) : int|string
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
	public function dayOfWeek(bool $iso_8601 = true) : int
	{
		return $this->format($iso_8601 ? self::DAY_OF_WEEK_ISO : self::DAY_OF_WEEK);
	}

	//------------------------------------------------------------------------------------- dayOfYear
	/**
	 * Returns the number of the day in the year, starting from 0
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
		return match ($unit) {
			self::DAY   => 1,
			self::MONTH => $this->format(self::DAYS_IN_MONTH),
			self::WEEK  => 7,
			self::YEAR  => (clone $this)->toEndOf(self::YEAR)->format(self::DAY_OF_YEAR),
			default     => null
		};
	}

	//----------------------------------------------------------------------------------- daysInMonth
	/**
	 * Returns the number of days in the given month
	 *
	 * @deprecated daysIn is its generic version
	 * @see daysIn
	 */
	public function daysInMonth() : int
	{
		return $this->format(self::DAYS_IN_MONTH);
	}

	//-------------------------------------------------------------------------------------- earliest
	/** Returns the earliest (ie smaller, older) date from the main object and a list of dates */
	public function earliest(Date_Time ...$dates) : static
	{
		$earliest = $this;
		foreach ($dates as $date) {
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
	 */
	public static function empty() : static
	{
		return static::min();
	}

	//---------------------------------------------------------------------------------------- format
	public function format(string $format) : string
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
	 * @throws Exception
	 */
	public static function fromISO(string $date, bool $max = false) : static
	{
		return (!empty($date) && !str_starts_with($date, '0000'))
			? new static(
				(strlen($date) >= 19)
					? $date
					: ($date . substr(($max ? self::$max_date : '2000-01-01 00:00:00'), strlen($date)))
			)
			: new static($max ? self::$max_date : self::$min_date);
	}

	//------------------------------------------------------------------------------------ fromString
	/** @throws Exception */
	public static function fromString(string $string) : ?static
	{
		return static::fromISO($string);
	}

	//-------------------------------------------------------------------------------------------- is
	public function is(Date_Time|string|null $date) : bool
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
	 */
	public function isAfter(Date_Time|string|null $date_time, bool $null_is_late = false) : bool
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
	 */
	public function isAfterOrEqual(Date_Time|string|null $date_time, bool $null_is_late = false)
		: bool
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
	 */
	public function isBefore(Date_Time|string|null $date_time, bool $null_is_late = false) : bool
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
	 */
	public function isBeforeOrEqual(Date_Time|string|null $date_time, bool $null_is_late = false)
		: bool
	{
		return isset($date_time) ? ($this->toISO(false) <= strval($date_time)) : $null_is_late;
	}

	//--------------------------------------------------------------------------------------- isEmpty
	/**
	 * Returns true if date is empty (equals to the min() or the max() date)
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
		return match ($unit) {
			self::DAY    => $this->format('H:i:s') === '23:59:59',
			self::HOUR   => $this->format('i') === '59',
			self::MINUTE => $this->format('s') === '59',
			self::MONTH  => $this->dayOfMonth() === $this->daysIn(Date_Time::MONTH),
			self::WEEK   => $this->dayOfWeek() === 7,
			self::YEAR   => $this->dayOfYear() === $this->daysIn(Date_Time::YEAR),
			default      => null
		};
	}

	//----------------------------------------------------------------------------------------- isMax
	/** Returns true if date is equals to the max() date */
	public function isMax() : bool
	{
		return ($this->toISO(false) >= self::$max_date);
	}

	//----------------------------------------------------------------------------------------- isMin
	/** Returns true if date is equals to the min() date */
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
	 * @see toEndOf
	 */
	public function lastDayOfMonth() : static
	{
		/** @noinspection PhpUnhandledExceptionInspection valid */
		return $this->isEmpty()
			? new static($this)
			: new static($this->format('Y-m-t 23:59:59'));
	}

	//---------------------------------------------------------------------------------------- latest
	/** Returns the latest (ie bigger, greater) date from the main object and a list of dates */
	public function latest(Date_Time ...$dates) : static
	{
		$latest = $this;
		foreach ($dates as $date) {
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
	 * @return_constant
	 */
	public static function max() : static
	{
		/** @noinspection PhpUnhandledExceptionInspection valid constant */
		return new static(self::$max_date);
	}

	//------------------------------------------------------------------------------------------- min
	/**
	 * Returns a minimal date time, far into the past considered as a date that none is before
	 *
	 * @return_constant
	 */
	public static function min() : static
	{
		/** @noinspection PhpUnhandledExceptionInspection valid constant */
		return new static(self::$min_date);
	}

	//----------------------------------------------------------------------------------------- month
	/**
	 * Returns a Date_Time for the month (goes to the beginning of the month)
	 *
	 * @deprecated use toBeginOf(Date_Time::MONTH) instead
	 * @example 'YYYY-MM-DD HH:II:SS' -> 'YYYY-MM-01 00:00:00'
	 * @see toBeginOf
	 */
	public function month() : static
	{
		if ($this->isEmpty()) {
			/** @noinspection PhpUnhandledExceptionInspection valid copy of $this */
			return new static($this);
		}
		/** @noinspection PhpUnhandledExceptionInspection valid format */
		return new static($this->format('Y-m'));
	}

	//------------------------------------------------------------------------------------------- now
	/** Returns current datetime */
	public static function now() : static
	{
		return new static();
	}

	//------------------------------------------------------------------------------------- nowMinute
	/** Returns current date-time, without initializing seconds */
	public static function nowMinute() : static
	{
		$date_time = new static();
		$date_time->setTime($date_time->format('H'), $date_time->format('i'));
		return $date_time;
	}

	//------------------------------------------------------------------------------------------- sub
	/**
	 * Increments a date for a given unit
	 *
	 * @param $interval DateInterval|integer
	 * @param $unit     string any of the Date_Time duration unit constants
	 * @return $this
	 */
	public function sub(DateInterval|int $interval, string $unit = self::DAY) : static
	{
		($interval instanceof DateInterval)
			? parent::sub($interval)
			: $this->add(-$interval, $unit);
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
	 * @return static
	 */
	public function toBeginOf(string $unit) : static
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
	 * @return static
	 * @see toBeginOf
	 */
	public function toBeginningOf(string $unit) : static
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
	 * @return static
	 */
	public function toEndOf(string $unit) : static
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
	 * @deprecated Please use month() instead
	 * @example    'YYYY-MM-DD HH:II:SS' -> 'YYYY-MM-01 00:00:00'
	 */
	public function toMonth() : static
	{
		if ($this->isMin()) {
			/** @noinspection PhpUnhandledExceptionInspection valid copy of $this */
			return new static($this);
		}
		/** @noinspection PhpUnhandledExceptionInspection valid $this and constant format */
		return new static($this->format('Y-m'));
	}

	//---------------------------------------------------------------------------------------- toNext
	public function toNext(int|string $what, string $unit = self::DAY) : static
	{
		return $unit
			? (clone $this)->add($what, $unit)
			: (is_numeric($what) ? (clone $this)->add($what) : (clone $this)->add(1, $what));
	}

	//------------------------------------------------------------------------------------ toPrevious
	public function toPrevious(int|string $what, string $unit = self::DAY) : static
	{
		return $unit
			? (clone $this)->sub($what, $unit)
			: (is_numeric($what) ? (clone $this)->sub($what) : (clone $this)->sub(1, $what));
	}

	//----------------------------------------------------------------------------------------- today
	/** Returns current date, with an empty time (00:00:00) */
	public static function today() : static
	{
		/** @noinspection PhpUnhandledExceptionInspection valid constant format */
		return new static(date('Y-m-d 00:00:00'));
	}

	//-------------------------------------------------------------------------------------- tomorrow
	/** Returns tomorrow date, with an empty time (00:00:00) */
	public static function tomorrow() : static
	{
		return static::today()->add(1);
	}

	//------------------------------------------------------------------------------------- yesterday
	/** Returns yesterday date, with an empty time (00:00:00) */
	public static function yesterday() : static
	{
		return static::today()->sub(1);
	}

}
