<?php
namespace ITRocks\Framework\Trigger;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Logger\Entry;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Trigger\Action\Status;
use ITRocks\Framework\Trigger\Schedule\Next_Calculation;
use ITRocks\Framework\User;
use ITRocks\Framework\View;

/**
 * Triggered action
 *
 * @list last, status, action
 * @representative action
 */
#[Store('trigger_actions')]
class Action
{

	//--------------------------------------------------------------------------------------- $action
	/**
	 * @max_length 50000
	 */
	public string $action = '';

	//-------------------------------------------------------------------------------------- $as_user
	/**
	 * @conditions keep_user=false
	 */
	public ?User $as_user;

	//------------------------------------------------------------------------------------ $keep_user
	/**
	 * Execute using the user that triggered the action
	 */
	public bool $keep_user = false;

	//----------------------------------------------------------------------------------------- $last
	/**
	 * Last execution time
	 *
	 * @user readonly
	 */
	public Date_Time|string $last;

	//----------------------------------------------------------------------------------------- $next
	/**
	 * Next scheduled time, if this is a scheduled action
	 *
	 * @user readonly
	 */
	public Date_Time|string $next;

	//--------------------------------------------------------------------------------------- $parent
	/**
	 * @user invisible
	 */
	public ?Action $parent;

	//--------------------------------------------------------------------------- $request_identifier
	/**
	 * This identifier, if set, matches Logger\Entry\Data::$request_identifier
	 */
	public string $request_identifier = '';

	//--------------------------------------------------------------------------------------- $status
	/**
	 * @values Status::const
	 */
	public string $status = Status::PENDING;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(string $action = null, Date_Time $next = null)
	{
		if (isset($action)) $this->action = $action;
		if (isset($next))   $this->next   = $next;
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return $this->action;
	}

	//--------------------------------------------------------------------------------------- execute
	/**
	 * Asynchronous execution of an action : this plans the action for execution
	 *
	 * To really launch planned actions, you must make /ITRocks/Framework/Trigger/Server/run as daemon
	 *
	 * @param $object object|string|null object or class name
	 * @return ?static scheduled action
	 */
	public function execute(object|string $object = null) : ?static
	{
		// can execute an action twice only if it is not running nor planned for now at this time
		$now = Date_Time::now();
		if (($this->status === Status::PENDING) && $this->next->isBeforeOrEqual($now)) {
			return null;
		}

		if (is_object($object)) {
			$class_name = get_class($object);
		}
		elseif (is_string($object)) {
			$class_name = $object;
			$object     = null;
		}
		else {
			$class_name = null;
		}

		$this->next = $now;

		// simple execution of the action itself
		if (
			!$this->keep_user
			&& ($this->status !== Action\Status::STATIC)
			&& !str_contains($this->action, '{')
		) {
			Dao::write($this, Dao::only('next'));
		}

		// the action contains dynamic {class} or {object} or keeps user : execute a clone of the action
		else {
			$action = $this->action;
			if ($class_name) {
				$action = str_replace(
					['{class}', '{object}', SL . SL],
					[Names::classToUri($class_name), View::link($object), SL],
					$action
				);
			}
			$as_user = $this->keep_user ? User::current() : null;
			$status  = Status::PENDING;
			if (Dao::searchOne(
				[
					'action'    => $action,
					'as_user'   => $as_user,
					'keep_user' => $this->keep_user,
					'next'      => Func::lessOrEqual($this->next),
					'status'    => $status
				],
				Action::class
			)) {
				return null;
			}
			$parent = clone $this;
			Dao::disconnect($this);
			$this->action  = $action;
			$this->as_user = $as_user;
			$this->parent  = $parent;
			$this->status  = $status;
			Dao::write($this);
		}
		return $this;
	}

	//----------------------------------------------------------------------------------- getLogEntry
	public function getLogEntry() : Entry
	{
		return Dao::searchOne(['data.request_identifier' => $this->request_identifier], Entry::class);
	}

	//------------------------------------------------------------------------------------------ next
	/**
	 * call this when action launch is validated (its execution may not be effective, and come later)
	 *
	 * - scheduled action : calculate and update next execution time
	 * - one-shot action : delete the action
	 *
	 * @param $last       Date_Time|null the last execution time @default Date_Time::now
	 * @param $write_last boolean update last execution time using $last / now
	 */
	public function next(Date_Time $last = null, bool $write_last = false) : void
	{
		if ($write_last && $last && $last->isAfter($this->last)) {
			$this->last = $last;
			$only[]     = 'last';
		}

		$this->next = ($this->parent ?: $this)->nextExecutionTime($last) ?: Date_Time::max();
		$only[]     = 'next';

		if ($this->parent) {
			if (in_array('last', $only)) {
				$this->parent->last = $this->last;
			}
			$this->parent->next = $this->next;
			Dao::write($this->parent, $only);
		}

		Dao::write($this, Dao::getObjectIdentifier($this) ? $only : []);
	}

	//----------------------------------------------------------------------------- nextExecutionTime
	/**
	 * Calculate and return the next execution time, if scheduled
	 *
	 * This does not change the value of $next
	 *
	 * @param $last Date_Time|null the reference date time for calculation @default Date_Time::now
	 * @return ?Date_Time null if it's not a scheduled action : then it will never execute again
	 */
	protected function nextExecutionTime(Date_Time $last = null) : ?Date_Time
	{
		if (!$last) {
			$last = Date_Time::now();
		}

		$calculate = new Next_Calculation();
		$next      = Date_Time::max();
		/** @var $schedules Schedule[] */
		$schedules = Dao::search(['actions' => $this], Schedule::class);
		foreach ($schedules as $schedule) {
			$next = $next->earliest($calculate->next($schedule, $last));
		}

		$linked = $schedules
			|| Dao::search(['actions' => $this], Change::class)
			|| Dao::search(['actions' => $this], Feature::class);

		return $linked ? $next : null;
	}

	//------------------------------------------------------------------------------------------ wait
	/**
	 * Wait for an action execution to be complete
	 *
	 * @param $timeout         integer
	 * @param $pending_timeout integer
	 * @return boolean|string true if done, or Timeout status @values execution, pending
	 */
	public function wait(int $timeout = 30, int $pending_timeout = 5) : bool|string
	{
		$time = time();
		while (Dao::read($this)->status === Status::PENDING) {
			usleep(100000);
			if ((time() - $time) > $pending_timeout) {
				return 'pending';
			}
		}
		while (!in_array(Dao::read($this)->status, Status::COMPLETE_STATUSES)) {
			usleep(100000);
			if ((time() - $time) > $timeout) {
				return 'execution';
			}
		}
		return true;
	}

}
