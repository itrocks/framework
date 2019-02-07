<?php
namespace ITRocks\Framework\Trigger;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Trigger\Action\Status;
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

	//--------------------------------------------------------------------------------------- $action
	/**
	 * @max_length 50000
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
	 * @link DateTime
	 * @user readonly
	 * @var Date_Time
	 */
	public $last;

	//----------------------------------------------------------------------------------------- $next
	/**
	 * Next scheduled time, if this is a scheduled action
	 *
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

	//--------------------------------------------------------------------------- $request_identifier
	/**
	 * This identifier, if set, matches Logger\Entry\Data::$request_identifier
	 *
	 * @var string
	 */
	public $request_identifier;

	//--------------------------------------------------------------------------------------- $status
	/**
	 * @values Status::const
	 * @var string
	 */
	public $status = Status::PENDING;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $action string
	 * @param $next   Date_Time|null
	 */
	public function __construct($action = null, $next = null)
	{
		if (isset($action)) {
			$this->action = $action;
		}
		if (isset($next)) {
			$this->next = $next;
		}
	}

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
	 * Asynchronous execution of an action : this plans the action for execution
	 *
	 * To really launch planned actions, you must make /ITRocks/Framework/Trigger/Server/run as daemon
	 *
	 * @param $object object|string object or class name
	 * @return static|null scheduled action
	 */
	public function execute($object = null)
	{
		// can execute an action twice only if it is not running nor planned for now at this time
		$now = Date_Time::now();
		if (($this->status === Status::PENDING) && $this->next->isBeforeOrEqual($now)) {
			return null;
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
		if (
			!$this->keep_user
			&& ($this->status !== Action\Status::STATIC)
			&& (strpos($this->action, '{') === false)
		) {
			Dao::write($this, Dao::only('next'));
		}

		// the action contains dynamic {class} or {object} or keeps user : execute a clone of the action
		else {
			$action = str_replace(
				['{class}', '{object}', SL . SL],
				[Names::classToUri($class_name), View::link($object), SL],
				$this->action
			);
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

	//------------------------------------------------------------------------------------------ next
	/**
	 * call this when action launch is validated (its execution may not be effective, and come later)
	 *
	 * - scheduled action : calculate and update next execution time
	 * - one-shot action : delete the action
	 *
	 * @param $last       Date_Time the last execution time @default Date_Time::now
	 * @param $write_last boolean update last execution time using $last / now
	 */
	public function next(Date_Time $last = null, $write_last = false)
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
