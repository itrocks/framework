<?php
namespace ITRocks\Framework\Traits;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Tools\Period;

/**
 * Use it into your objects that have begin and end dates
 */
trait Has_Begin_End_Dates
{

	//----------------------------------------------------------------------------------- $begin_date
	/**
	 * @link DateTime
	 * @var Date_Time
	 */
	public $begin_date;

	//------------------------------------------------------------------------------------- $end_date
	/**
	 * @link DateTime
	 * @var Date_Time
	 */
	public $end_date;

	//---------------------------------------------------------------------------------- datesOverlap
	/**
	 * Returns true if begin and end dates for the current object overlap dates of $with
	 *
	 * @param $with Has_Begin_End_Dates
	 * @return boolean
	 */
	public function datesOverlap($with)
	{
		$this_period = Builder::create(Period::class, [$this->begin_date, $this->end_date]);
		$with_period = Builder::create(Period::class, [$with->begin_date, $with->end_date]);
		return !$this_period->out($with_period);
	}

}
