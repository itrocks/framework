<?php
namespace SAF\Framework\Tools;

use DateInterval;
use DateTime;
use DateTimeZone;

/**
 * This class extends php's DateTime class : you should use this to be SAF compatible
 */
class Date_Time extends DateTime
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

	//------------------------------------------------------------------------------------- $max_date
	/**
	 * The max date
	 *
	 * @var string
	 */
	private static $max_date = '2999-12-31 23:59:59';

	//------------------------------------------------------------------------------------- $min_date
	/**
	 * The min date
	 *
	 * @var string
	 */
	private static $min_date = '0001-01-01 00:00:00';

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
		if (is_int($time)) {
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
		elseif (is_integer($quantity)) {
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
				return parent::add($interval);
			}
		}
		return $this;
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
		return !(intval($this->toISO()) + 0);
	}

	//----------------------------------------------------------------------------------------- isMax
	/**
	 * Returns true if date is equals to the max() date
	 *
	 * @return boolean
	 */
	public function isMax()
	{
		return ($this->toISO(false) === self::$max_date);
	}

	//----------------------------------------------------------------------------------------- isMin
	/**
	 * Returns true if date is equals to the min() date
	 *
	 * @return boolean
	 */
	public function isMin()
	{
		return ($this->toISO(false) === self::$min_date);
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
	 * Return current date-time
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
			? parent::sub($quantity)
			: $this->add(-$quantity, $unit);
	}

	//----------------------------------------------------------------------------------------- toISO
	/**
	 * @param $empty_min_max boolean If true, returns an empty string for zero or max dates
	 * @return string
	 */
	public function toISO($empty_min_max = true)
	{
		$format = $this->format('Y-m-d H:i:s');
		return ($empty_min_max && (($format === self::$min_date) || ($format === self::$max_date)))
			? '' : $this->format('Y-m-d H:i:s');
	}

}
