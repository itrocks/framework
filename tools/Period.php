<?php
namespace ITRocks\Framework\Tools;

use ITRocks\Framework\Locale\Loc;

/**
 * Representation of a period between two Date_Time
 */
class Period
{

	//---------------------------------------------------------------------------------------- $begin
	/**
	 * @var Date_Time
	 */
	public Date_Time $begin;

	//------------------------------------------------------------------------------------------ $end
	/**
	 * @var Date_Time
	 */
	public Date_Time $end;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Period constructor.
	 *
	 * @param $begin  Date_Time
	 * @param $end    Date_Time
	 */
	public function __construct(Date_Time $begin, Date_Time $end)
	{
		if ($end->isMin()) {
			$end = Date_Time::max();
		}
		if ($begin->isAfter($end)) {
			$this->end   = $begin;
			$this->begin = $end;
		}
		else {
			$this->begin = $begin;
			$this->end   = $end;
		}
	}

	//--------------------------------------------------------------------------------------- exclude
	/**
	 * Return current period which is not in the period passed in parameters
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $period Period
	 * @return Period[] Can return 0, 1 or 2 periods
	 */
	public function exclude(Period $period) : array
	{
		if ($this->out($period)) {
			return [clone $this];
		}
		else if ($this->in($period)) {
			return [];
		}
		else {
			$periods = [];
			if ($this->begin->isBefore($period->begin)) {
				/** @noinspection PhpUnhandledExceptionInspection from Date_Time */
				$end = new Date_Time($period->begin);
				$end->add(-1, Date_Time::SECOND);
				$periods[] = new Period($this->begin, $end);
			}
			if ($this->end->isAfter($period->end)) {
				/** @noinspection PhpUnhandledExceptionInspection from Date_Time */
				$begin = new Date_Time($period->end);
				$begin->add(1, Date_Time::SECOND);
				$periods[] = new Period($begin, $this->end);
			}
			return $periods;
		}
	}

	//---------------------------------------------------------------------------------------- format
	/**
	 * Return difference between begin and end date
	 *
	 * @param $format string force format, if not set only outputs relevant value
	 * @return string
	 * @see DateInterval::format()
	 */
	public function format(string $format = '') : string
	{
		$diff = $this->begin->diff($this->end, true);
		if (!$format) {
			$format = [];
			if ($diff->days) {
				$format[] = '%a' . SP . Loc::tr(($diff->days > 1) ? 'days' : 'day');
			}
			if ($diff->h) {
				$format[] = '%h' . SP . Loc::tr(($diff->h > 1) ? 'hours' : 'hour');
			}
			if ($diff->i) {
				$format[] = '%i' . SP . Loc::tr(($diff->i > 1) ? 'minutes' : 'minute');
			}
			if ($diff->s) {
				$format[] = '%s' . SP . Loc::tr(($diff->s > 1) ? 'seconds' : 'second');
			}
			$format = join(SP, $format);
		}
		return $diff->format($format);
	}

	//-------------------------------------------------------------------------------------------- in
	/**
	 * Check if current period is contained in $period
	 *
	 * @param $period Period
	 * @return boolean
	 */
	public function in(Period $period) : bool
	{
		return $this->begin->isAfterOrEqual($period->begin)
			&& $this->end->isBeforeOrEqual($period->end);
	}

	//------------------------------------------------------------------------------------- intersect
	/**
	 * Return a period of current period in main period
	 *
	 * @param $main_period Period
	 * @return ?Period
	 */
	public function intersect(Period $main_period) : ?Period
	{
		$begin = $this->begin->latest($main_period->begin);
		$end   = $this->end->earliest($main_period->end);
		return $begin->isBeforeOrEqual($end) ? new Period($begin, $end) : null;
	}

	//------------------------------------------------------------------------------------------- out
	/**
	 * Check if period is out of current period (no intersection between two periods)
	 *
	 * @param $period Period
	 * @return boolean
	 */
	public function out(Period $period) : bool
	{
		return $this->end->isBefore($period->begin) || $this->begin->isAfter($period->end);
	}

	//-------------------------------------------------------------------------------------- toMonths
	/**
	 * Return all months contained in period
	 *
	 * @return Date_Time[]
	 */
	public function toMonths() : array
	{
		$start  = $this->begin->toBeginOf(Date_Time::MONTH);
		$stop   = $this->end->toBeginOf(Date_Time::MONTH);
		$months = [];
		while ($start->isBefore($stop)) {
			$months[] = clone $start;
			$start->add(1, Date_Time::MONTH);
		}
		$months[] = $stop;
		return $months;
	}

	//----------------------------------------------------------------------------------------- union
	/**
	 * Return the union of the 2 periods.
	 *
	 * @param $period Period
	 * @return Period[] Can return 1 or 2 periods.
	 */
	public function union(Period $period) : array
	{
		if ($this->out($period)) {
			return [clone $this, clone $period];
		}
		else {
			$begin = $this->begin->earliest($period->begin);
			$end   = $this->end->latest($period->end);
			return [new Period($begin, $end)];
		}
	}

}
