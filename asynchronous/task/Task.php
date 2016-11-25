<?php

namespace ITRocks\Framework\Asynchronous;

use Exception;
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

	//-------------------------------------------------------------------------------------- $request
	/**
	 * @link Object
	 * @composite
	 * @var Request
	 */
	public $request;

	//--------------------------------------------------------------------------------------- $worker
	/**
	 * @link Object
	 * @store json
	 * @var Worker
	 */
	public $worker;

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

	//--------------------------------------------------------------------------------------- $status
	/**
	 * Status of debit
	 *
	 * @values pending, in_progress, finished, error, stopped
	 * @var string
	 */
	public $status = self::PENDING;

	//--------------------------------------------------------------------------------------- execute
	public function execute()
	{
		$this->worker->execute();
	}

	//--------------------------------------------------------------------------------------- started
	public function started()
	{
		$this->begin_date = new Date_Time();
		$this->status = self::IN_PROGRESS;
		$this->worker->started();
		Dao::write($this);
	}

	//-------------------------------------------------------------------------------------- finished
	public function finished()
	{
		$this->end_date = new Date_Time();
		$this->status = self::FINISHED;
		$this->worker->finished();
		Dao::write($this);
	}

	//----------------------------------------------------------------------------------------- error
	/**
	 * @param $e Exception Exception launched
	 */
	public function error(Exception $e)
	{
		$this->end_date = new Date_Time();
		$this->status = self::ERROR;
		$this->worker->error($e);
		Dao::write($this);
	}

	//------------------------------------------------------------------------------------ canExecute
	/**
	 * @return boolean
	 */
	public function canExecute()
	{
		return $this->status === self::PENDING;
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

}
