<?php
namespace ITRocks\Framework\Trigger;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Traits\Is_Immutable;
use ITRocks\Framework\Trigger\Schedule\Next_Calculation;
use ITRocks\Framework\User;
use ITRocks\Framework\View;

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
	 * @conditions keep_user=false
	 * @link Object
	 * @var User
	 */
	public $as_user;

	//------------------------------------------------------------------------------------ $keep_user
	/**
	 * Execute using the user that triggered the action
	 *
	 * @var boolean
	 */
	public $keep_user;

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

	//--------------------------------------------------------------------------------------- $parent
	/**
	 * @link Object
	 * @user invisible
	 * @var Action
	 */
	public $parent;

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

	//--------------------------------------------------------------------------------------- execute
	/**
	 * @param $object object|string object or class name
	 */
	public function execute($object = null)
	{
		// can execute an action twice only if it is not running nor planned for now at this time
		$now = Date_Time::now();
		if ($this->next->isBeforeOrEqual($now) && !$this->running) {
			return;
		}

		if (is_string($object)) {
			$class_name = $object;
			$object     = null;
		}
		else {
			$class_name = get_class($object);
		}

		$this->next = $now;

		// simple execution of the action itself
		if ((strpos($this->action, '{') === false) && !$this->keep_user) {
			Dao::write($this, Dao::only('next'));
		}

		// the action contains dynamic {class} or {object} or keeps user : execute a clone of the action
		else {
			$this->parent = clone $this;
			Dao::disconnect($this);
			$this->action = str_replace(
				['{class}', '{object}'],
				[Names::classToUri($class_name), View::link($object)],
				$this->action
			);
			if ($this->keep_user) {
				$this->as_user   = User::current();
				$this->keep_user = false;
			}
			Dao::write($this);
		}
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
			if ($this->parent) {
				$this->parent->last = $last;
				Dao::write($this->parent, Dao::only('last'));
			}
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

		$linked = $schedules
			|| Dao::search(['actions' => $this], Change::class)
			|| Dao::search(['actions' => $this], Feature::class);

		return $linked ? $next : null;
	}

}
