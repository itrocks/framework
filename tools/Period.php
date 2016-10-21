<?php
namespace SAF\Framework\Tools;

/**
 * Representation of a period between two Date_Time
 */
class Period
{

	//---------------------------------------------------------------------------------------- $begin
	/**
	 * @var Date_Time
	 */
	public $begin;

	//------------------------------------------------------------------------------------------ $end
	/**
	 * @var Date_Time
	 */
	public $end;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Period constructor.
	 *
	 * @param $begin  Date_Time
	 * @param $end    Date_Time
	 */
	public function __construct(Date_Time $begin, Date_Time $end)
	{
		if ($begin->isAfter($end) && !$end->isEmpty()) {
			$this->end    = $begin;
			$this->begin  = $end;
		}
		else {
			$this->begin  = $begin;
			$this->end    = $end;
		}
	}

	//--------------------------------------------------------------------- getMonthsDateTimeInPeriod
	/**
	 * Return all months contained in period
	 *
	 * @return Date_Time[]
	 */
	public function getMonthsDateTimeInPeriod()
	{
		$start = $this->begin->toMonth();
		$stop = $this->end->toMonth();
		$months = [];
		while ($start->isBefore($stop)) {
			$months[] = clone $start;
			$start->add(1, Date_Time::MONTH);
		}
		$months[] = $stop;
		return $months;
	}

	//-------------------------------------------------------------------------------------------- in
	/**
	 * Check if current period is contained in $period
	 *
	 * @param $period Period
	 * @return boolean
	 */
	public function in(Period $period)
	{
		return $this->begin->isAfterOrEqual($period->begin)
		&& $this->begin->isBeforeOrEqual($period->end)
		&& $this->end->isAfterOrEqual($period->begin)
		&& $this->end->isBeforeOrEqual($period->end);
	}

	//------------------------------------------------------------------------------------- intersect
	/**
	 * Return a period of current period in main period
	 *
	 * @param $main_period Period
	 * @return Period
	 */
	public function intersect(Period $main_period)
	{
		if ($this->in($main_period)) {
			return $this;
		}
		else if ($main_period->in($this)) {
			return $main_period;
		}
		else if ($main_period->out($this)) {
			return null;
		}
		else if ($this->intersectBegin($main_period)) {
			return new Period($main_period->begin, $this->end);
		}
		else if ($this->intersectEnd($main_period)) {
			return new Period($this->begin, $main_period->end);
		}
		return null;
	}

	//-------------------------------------------------------------------------------- intersectBegin
	/**
	 * If intersect begin of other period
	 *
	 * @param $period Period
	 * @return boolean
	 */
	public function intersectBegin(Period $period)
	{
		return $this->begin->isBefore($period->begin)
		&& $this->end->isAfterOrEqual($period->begin)
		&& $this->end->isBeforeOrEqual($period->end);
	}

	//---------------------------------------------------------------------------------- intersectEnd
	/**
	 * If intersect end of other period
	 *
	 * this->begin::in(period), period > this->end
	 *
	 * @param $period Period
	 * @return boolean
	 */
	public function intersectEnd(Period $period)
	{
		return $this->begin->isAfterOrEqual($period->begin)
		&& $this->begin->isBeforeOrEqual($period->end)
		&& $this->end->isAfter($period->end);
	}

	//------------------------------------------------------------------------------------------- out
	/**
	 * Check if period is out of current period (no intersection between two periods)
	 *
	 * @param $period Period
	 * @return boolean
	 */
	public function out(Period $period)
	{
		return $this->begin->isBefore($period->begin)
		&& $this->end->isBefore($period->begin)
		|| $this->begin->isAfter($period->end)
		&& ($this->end->isAfter($period->end) || $this->end->isEmpty() && !$period->end->isEmpty());
	}
}
