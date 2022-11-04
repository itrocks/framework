<?php
namespace ITRocks\Framework\Tools;

use DateInterval;
use DateTime;
use DateTimeZone;

/**
 * Rich Date interval class
 *
 * Date_Interval could not extend DateInterval because of the internal 'days' property,
 * which could not be set.
 */
abstract class Date_Interval
{

	//------------------------------------------------------------------------------- DAY_TIME_FORMAT
	const DAY_TIME_FORMAT = 'P%aDT%hH%iM%sS';

	//------------------------------------------------------------------------------ duration formats
	const DAYS    = '%R%a';
	const HOURS   = 'hours';
	const MINUTES = 'minutes';
	const MONTHS  = 'months';
	const SECONDS = 'seconds';
	const WEEKS   = 'weeks';
	const YEARS   = 'years';

	//------------------------------------------------------------------------------------ EMPTY_SPEC
	const EMPTY_SPEC = 'PT0S';

	//----------------------------------------------------------------------------------- FULL_FORMAT
	const FULL_FORMAT = 'P%yY%mM%dDT%hH%iM%sS';

	//---------------------------------------------------------------------------------------- adjust
	/**
	 * Adjusts a DateInterval to have all fields sort out.
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @example
	 * P25H will be P1D1H.
	 * @notice
	 * It's not possible to adjust year/month as it would be dependant from start date)
	 * @param $interval DateInterval
	 * @return DateInterval
	 */
	public static function adjust(DateInterval $interval) : DateInterval
	{
		/** @noinspection PhpUnhandledExceptionInspection constant date */
		$begin_date = new DateTime('2000-01-01', new DateTimeZone('UTC'));
		$end_date   = clone $begin_date;
		$end_date->add($interval);
		$interval = $begin_date->diff($end_date);
		// Using %a to have the real number of days
		/** @noinspection PhpUnhandledExceptionInspection generated format is valid */
		$adjusted_interval         = new DateInterval($interval->format(static::DAY_TIME_FORMAT));
		$adjusted_interval->invert = $interval->invert;
		return $adjusted_interval;
	}

	//---------------------------------------------------------------------------------- fromDuration
	/**
	 * Creates an interval knowing the duration in seconds
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @example You can easily get a Date_Interval between two timestamps with this call
	 *          Date_Interval::createFromDuration($timestamp2 - $timestamp1)
	 * @notice  The result can only be time + number of days, year/month will be null as they are
	 *          start date dependant
	 * @param   $duration integer The duration in seconds, may be negative
	 * @return  DateInterval
	 */
	public static function fromDuration(int $duration) : DateInterval
	{
		// Initialize a DateInterval in seconds
		/** @noinspection PhpUnhandledExceptionInspection constant format is valid */
		$interval    = new DateInterval(static::EMPTY_SPEC);
		$interval->s = $duration;
		return static::adjust($interval);
	}

	//--------------------------------------------------------------------------------------- getDays
	/**
	 * @param $interval DateInterval
	 * @return integer
	 * @throws Date_Interval_Exception
	 */
	private static function getDays(DateInterval $interval) : int
	{
		if (!$interval->days && ($interval->m || $interval->y)) {
			throw new Date_Interval_Exception();
		}
		return $interval->days ?: $interval->d;
	}

	//----------------------------------------------------------------------------------------- round
	/**
	 * @param $duration   float
	 * @param $round_mode integer|string @values PHP_CEIL, PHP_FLOOR, PHP_ROUND_HALF_*
	 * @return integer
	 */
	private static function round(float $duration, int|string $round_mode) : int
	{
		switch ($round_mode) {
			case PHP_CEIL:
				return ceil($duration);
			case PHP_FLOOR:
				return floor($duration);
		}
		return round($duration, 0, $round_mode);
	}

	//---------------------------------------------------------------------------------------- toDays
	/**
	 * Returns the date interval in number of days
	 *
	 * @param $interval   DateInterval
	 * @param $round_mode integer|string @values PHP_CEIL, PHP_FLOOR, PHP_ROUND_HALF_*
	 * @param $absolute   boolean
	 * @return integer
	 * @throws Date_Interval_Exception
	 */
	public static function toDays(
		DateInterval $interval, int|string $round_mode = PHP_CEIL, bool $absolute = false
	) : int
	{
		return static::round(static::toSeconds($interval, $absolute) / 86400, $round_mode);
	}

	//--------------------------------------------------------------------------------------- toHours
	/**
	 * Returns the date interval in number of hours
	 *
	 * @param $interval   DateInterval
	 * @param $round_mode integer|string @values PHP_CEIL, PHP_FLOOR, PHP_ROUND_HALF_*
	 * @param $absolute   boolean
	 * @return integer
	 * @throws Date_Interval_Exception
	 */
	public static function toHours(
		DateInterval $interval, int|string $round_mode = PHP_CEIL, bool $absolute = false
	) : int
	{
		return static::round(static::toSeconds($interval, $absolute) / 3600, $round_mode);
	}

	//------------------------------------------------------------------------------------- toMinutes
	/**
	 * Returns the date interval in number of minutes
	 *
	 * @param $interval   DateInterval
	 * @param $round_mode integer|string @values PHP_CEIL, PHP_FLOOR, PHP_ROUND_HALF_*
	 * @param $absolute   boolean
	 * @return integer
	 * @throws Date_Interval_Exception
	 */
	public static function toMinutes(
		DateInterval $interval, int|string $round_mode = PHP_CEIL, bool $absolute = false
	) : int
	{
		return static::round(static::toSeconds($interval, $absolute) / 60, $round_mode);
	}

	//------------------------------------------------------------------------------------- toSeconds
	/**
	 * Returns the date interval in number of seconds
	 *
	 * @param $interval DateInterval
	 * @param $absolute boolean
	 * @return integer
	 * @throws Date_Interval_Exception
	 */
	public static function toSeconds(DateInterval $interval, bool $absolute = false) : int
	{
		$duration  = static::getDays($interval) * 86400;
		$duration += $interval->h * 3600;
		$duration += $interval->i * 60;
		$duration += $interval->s;
		return ($absolute || !$interval->invert) ? abs($duration) : -abs($duration);
	}

	//--------------------------------------------------------------------------------------- toWeeks
	/**
	 * Returns the date interval in number of weeks
	 *
	 * @param $interval   DateInterval
	 * @param $round_mode integer|string @values PHP_CEIL, PHP_FLOOR, PHP_ROUND_HALF_*
	 * @param $absolute   boolean
	 * @return integer
	 * @throws Date_Interval_Exception
	 */
	public static function toWeeks(
		DateInterval $interval, int|string $round_mode = PHP_CEIL, bool $absolute = false
	) : int
	{
		return static::round(static::toSeconds($interval, $absolute) / 604800, $round_mode);
	}

}
