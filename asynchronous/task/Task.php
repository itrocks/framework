<?php

namespace ITRocks\Framework\Asynchronous;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Mapper\Component;
use ITRocks\Framework\Tools\Date_Time;

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
	 * @var string
	 */
	public $error;

	//---------------------------------------------------------------------------------------- $group
	/**
	 * Execution group.
	 * Used for distributed task in many servers
	 * @var integer
	 */
	public $group = 1;

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
	 * @return string
	 */
	public function calculateDuration()
	{
		$end_date = $this->end_date && !$this->end_date->isEmpty()
			? $this->end_date : new Date_Time();
		if ($end_date && $this->begin_date && !$this->begin_date->isEmpty()) {
			$diff = $this->begin_date->diff($end_date, true);
			$format = [];
			if ($diff->h) {
				$format[] = $diff->h . SP . Loc::tr('hour');
			}
			if ($diff->i) {
				$format[] = $diff->i . SP . Loc::tr('minutes');
			}
			if ($diff->s) {
				$format[] = $diff->s . SP . Loc::tr('seconds');
			}
			return join(SP, $format);
		}
		return '';
	}

	//------------------------------------------------------------------------------------ canExecute
	/**
	 * @return boolean
	 */
	public function canExecute()
	{
		$status = $this->status === self::PENDING;
		if (!$status) {
			return false;
		}
		if ($this->condition) {
			return $this->condition->check();
		}
		return true;
	}

	//----------------------------------------------------------------------------------------- error
	public function error()
	{
		$this->end_date = new Date_Time();
		$this->status = self::ERROR;
		Dao::write($this);
	}

	//-------------------------------------------------------------------------------------- finished
	public function finished()
	{
		$this->end_date = new Date_Time();
		$this->status = self::FINISHED;
		Dao::write($this);
	}

	//------------------------------------------------------------------------------------------- run
	public function run()
	{
		$this->worker->task = $this;
		$this->worker->run();
	}

	//--------------------------------------------------------------------------------------- started
	public function started()
	{
		$this->begin_date = new Date_Time();
		$this->status = self::IN_PROGRESS;
		Dao::write($this);
	}

}
