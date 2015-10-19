<?php
namespace SAF\Framework\Tools;

use DateInterval;

/**
 * Rich Date interval class
 */
class Date_Interval extends DateInterval
{

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
