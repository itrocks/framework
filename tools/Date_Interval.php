<?php
namespace ITRocks\Framework\Tools;

use DateInterval;

/**
 * Rich Date interval class
 */
class Date_Interval extends DateInterval
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Date_Interval constructor
	 *
	 * @link http://php.net/manual/en/dateinterval.construct.php
	 * @param $interval_spec string
	 * @param $invert        boolean
	 */
	public function __construct($interval_spec, $invert = null)
	{
		parent::__construct($interval_spec);
		if (isset($invert)) {
			$this->invert = $invert;
		}
	}

	//--------------------------------------------------------------------------------------- compare
	/**
	 * Returns true if interval is positive, false if negative, or 0 if equal
	 */
	public function compare()
	{
		return ($this->s || $this->i || $this->h || $this->d || $this->m || $this->y)
			? ($this->invert ? 1 : -1)
			: 0;
	}

	//---------------------------------------------------------------------------- createFromDuration
	/**
	 * Creates an interval knowing the duration in seconds
	 *
	 * @example
	 * You can easily get a Date_Interval between two timestamps with this call :
	 * Date_Interval::createFromDuration($timestamp2 - $timestamp1)
	 *
	 * @param $duration integer The duration in seconds, may be negative
	 * @return self
	 */
	public static function createFromDuration($duration)
	{
		$invert = false;
		if ($duration < 0) {
			$duration = -$duration;
			$invert = true;
		}
		$sprintf_arguments = explode('-', date('Y-m-d-H-i-s', $duration));
		$sprintf_arguments[0] -= 1970;
		$sprintf_arguments[1] -= 1;
		$sprintf_arguments[2] -= 1;
		$sprintf_arguments[3] -= 1;
		array_unshift($sprintf_arguments, 'P%sY%sM%sDT%sH%sM%sS');
		$interval_spec = call_user_func_array('sprintf', $sprintf_arguments);
		return new Date_Interval($interval_spec, $invert);
	}

	//---------------------------------------------------------------------------------------- toTime
	/**
	 * Returns the date interval in time format (number of seconds)
	 *
	 * @param $absolute boolean
	 * @return integer
	 */
	public function toTime($absolute = false)
	{
		return (($this->invert && !$absolute) ? -1 : 1) * (
			$this->s
			+ $this->i * 60
			+ $this->h * 3600
			+ $this->d * 86400
			+ $this->m * 2592000
			+ $this->y * 31104000
		);
	}

}
