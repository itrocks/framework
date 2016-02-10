<?php
namespace SAF\Framework\Tools;

use DateInterval;
use DateTime;
use DateTimeZone;

/**
 * This class extends php's DateTime class : you should use this to be SAF compatible
 */
class Date_Time extends DateTime implements Can_Be_Empty, Stringable
{

	//----------------------------------------------------------------------- duration unit constants
	/** Duration unit : hour */
	const DAY    = 'day';
	const HOUR   = 'hour';
	const MINUTE = 'minute';
	const MONTH  = 'month';
	const SECOND = 'second';
	const WEEK   = 'week';
	const YEAR   = 'year';

	//------------------------------------------------------------------------- date format constants
	const DAY_OF_MONTH  = 'd';
	const DAY_OF_WEEK   = 'w';
	const DAYS_IN_MONTH = 't';

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
	public function __construct($time = 'now', DateTimeZone $timezone = null)
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
		return $this->toISO();
	}

	//------------------------------------------------------------------------------------------- add
	/**
	 * Increments a date for a given unit
	 *
	 * @param $quantity integer|DateInterval
	 * @param $unit     string any of the Date_Time duration unit constants
	 * @return Date_Time
	 */
	public function add($quantity, $unit = Date_Time::DAY)
	{
		if ($quantity instanceof DateInterval) {
			return parent::add($quantity);
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
				return new Date_Time(parent::add($interval));
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

	//----------------------------------------------------------------------------------- daysInMonth
	/**
	 * Returns the number of days in the given month
	 *
	 * @return integer
	 */
	public function daysInMonth()
	{
		return $this->format('d');
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
		$interval = new Date_Interval($parent_diff->format('P%yY%mM%dDT%hH%iM%sS'));
		$interval->invert = $parent_diff->invert;
		return $interval;
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
	 * @param $date string
	 * @return Date_Time
	 */
	public static function fromISO($date)
	{
		return (!empty($date) && (substr($date, 0, 4) !== '0000'))
			? new Date_Time($date . substr('2000-01-01 00:00:00', strlen($date)))
			: new Date_Time(self::$min_date);
	}

	//------------------------------------------------------------------------------------ fromString
	/**
	 * @param $string string
	 * @return self
	 */
	public function fromString($string)
	{
		$this->fromISO($string);
		return $this;
	}

	//-------------------------------------------------------------------------------------------- is
	/**
	 * @param $begin_date Date_Time
	 * @return boolean
	 */
	public function is(Date_Time $begin_date)
	{
		return $this->toISO() === $begin_date->toISO();
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
		return isset($date_time)
			? ($this->toISO() > (is_string($date_time) ? $date_time : $date_time->toISO()))
			: !$null_is_late;
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
		return isset($date_time)
			? ($this->toISO() >= (is_string($date_time) ? $date_time : $date_time->toISO()))
			: !$null_is_late;
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
		return isset($date_time)
			? ($this->toISO() < (is_string($date_time) ? $date_time : $date_time->toISO()))
			: $null_is_late;
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
		return isset($date_time)
			? ($this->toISO() <= (is_string($date_time) ? $date_time : $date_time->toISO()))
			: $null_is_late;
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
	public function sub($quantity, $unit = Date_Time::DAY)
	{
		return ($quantity instanceof DateInterval)
			? new Date_Time(parent::sub($quantity))
			: $this->add(-$quantity, $unit);
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

}
