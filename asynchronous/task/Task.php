<?php

namespace ITRocks\Framework\Asynchronous;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Mapper\Component;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Tools\Period;

/**
 * @business
 * @features
 * @set Asynchronous_Tasks
 */
class Task
{
	use Component;

	//----------------------------------------------------------------------------------------- ERROR
	const ERROR = 'error';

	//-------------------------------------------------------------------------------------- FINISHED
	const FINISHED = 'finished';

	//----------------------------------------------------------------------------------- IN_PROGRESS
	const IN_PROGRESS = 'in_progress';

	//--------------------------------------------------------------------------------------- PENDING
	const PENDING = 'pending';

	//--------------------------------------------------------------------------------------- STOPPED
	const STOPPED = 'stopped';

	//----------------------------------------------------------------------------------- $begin_date
	/**
	 * @link DateTime
	 * @var Date_Time
	 */
	public $begin_date;

	//------------------------------------------------------------------------------------ $condition
	/**
	 * @link Object
	 * @store json
	 * @user readonly
	 * @var Condition
	 */
	public $condition;

	//------------------------------------------------------------------------------------- $end_date
	/**
	 * @link DateTime
	 * @var Date_Time
	 */
	public $end_date;

	//---------------------------------------------------------------------------------------- $error
	/**
	 * Error message
	 *
	 * @var string
	 */
	public $error;

	//---------------------------------------------------------------------------------------- $group
	/**
	 * Execution group.
	 * Used for distributed task in many execution
	 *
	 * @var integer
	 */
	public $group = 0;

	//-------------------------------------------------------------------------------------- $request
	/**
	 * @link Object
	 * @composite
	 * @var Request
	 */
	public $request;

	//--------------------------------------------------------------------------------------- $status
	/**
	 * Status of debit
	 *
	 * @values pending, in_progress, finished, error, stopped
	 * @var string
	 */
	public $status = self::PENDING;

	//--------------------------------------------------------------------------------------- $worker
	/**
	 * @link Object
	 * @store json
	 * @user readonly
	 * @var Worker
	 */
	public $worker;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	function __toString()
	{
		return strval($this->worker) . ' - ' . Loc::tr($this->status);
	}

	//----------------------------------------------------------------------------- calculateDuration
	/**
	 * Calculate difference between start and end time
	 *
	 * @return string
	 */
	public function calculateDuration()
	{
		$period = new Period($this->begin_date, $this->end_date);
		return $period->format();
	}

	//------------------------------------------------------------------------------------ canExecute
	/**
	 * Check if we can execute task
	 *
	 * @return boolean
	 */
	public function canExecute()
	{
		$status = $this->status === self::PENDING;
		if (!$status) {
			return false;
		}
		if ($this->condition) {
			$condition = $this->condition->check();
			return in_array($condition, [Condition::VALID]);
		}
		return true;
	}

	//----------------------------------------------------------------------------------------- error
	/**
	 * Call by worker run if error was happening
	 */
	public function error()
	{
		$this->end_date = new Date_Time();
		$this->status = self::ERROR;
		Dao::write($this);
	}

	//-------------------------------------------------------------------------------------- finished
	/**
	 * Call by worker when task is finished
	 */
	public function finished()
	{
		$this->end_date = new Date_Time();
		$this->status = self::FINISHED;
		Dao::write($this);
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * Call to run worker
	 */
	public function run()
	{
		$this->worker->task = $this;
		$this->worker->run();
	}

	//--------------------------------------------------------------------------------------- started
	/**
	 * Call when worker begin work
	 */
	public function started()
	{
		$this->begin_date = new Date_Time();
		$this->status = self::IN_PROGRESS;
		Dao::write($this);
	}

}
