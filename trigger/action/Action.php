<?php
namespace ITRocks\Framework\Trigger;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Traits\Is_Immutable;
use ITRocks\Framework\Trigger\Schedule\Next_Calculation;
use ITRocks\Framework\User;

/**
 * Triggered action
 *
 * @representative action
 * @store_name trigger_actions
 */
class Action
{
	use Is_Immutable;

	//--------------------------------------------------------------------------------------- $action
	/**
	 * @var string
	 */
	public $action;

	//-------------------------------------------------------------------------------------- $as_user
	/**
	 * @link Object
	 * @var User
	 */
	public $as_user;

	//----------------------------------------------------------------------------------------- $last
	/**
	 * Last execution time
	 *
	 * @immutable false
	 * @link DateTime
	 * @user readonly
	 * @var Date_Time
	 */
	public $last;

	//----------------------------------------------------------------------------------------- $next
	/**
	 * Next scheduled time, if this is a scheduled action
	 *
	 * @immutable false
	 * @link DateTime
	 * @user readonly
	 * @var Date_Time
	 */
	public $next;

	//-------------------------------------------------------------------------------------- $running
	/**
	 * Tell is this action is currently running or not
	 *
	 * @immutable false
	 * @var boolean
	 */
	public $running;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->action);
	}

	//------------------------------------------------------------------------------------------ next
	/**
	 * call this when action launch is validated (its execution may not be effective, and come later)
	 *
	 * - scheduled action : calculate and update next execution time
	 * - one-shot action : delete the action
	 *
	 * @param $last Date_Time the last execution time @default Date_Time::now
	 */
	public function next(Date_Time $last = null)
	{
		if ($next = $this->nextExecutionTime($last)) {
			$this->last = $last;
			$this->next = $next;
			Dao::write($this, Dao::only(['last', 'next']));
		}
		else {
			Dao::delete($this);
		}
	}

	//----------------------------------------------------------------------------- nextExecutionTime
	/**
	 * Calculate and return the next execution time, if scheduled
	 *
	 * This does not change the value of $next
	 *
	 * @param $last Date_Time the reference date time for calculation @default Date_Time::now
	 * @return Date_Time|null null if its not a scheduled action : then it will never execute again
	 */
	protected function nextExecutionTime($last = null)
	{
		if (!$last) {
			$last = Date_Time::now();
		}
		$calculate = new Next_Calculation();
		$next      = Date_Time::max();
		$schedules = Dao::search(['actions' => $this], Schedule::class);
		foreach ($schedules as $schedule) {
			$next = $next->earliest($calculate->next($schedule, $last));
		}
		return $schedules ? $next : null;
	}

}
