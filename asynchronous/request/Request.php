<?php
namespace ITRocks\Framework\Asynchronous;

use ITRocks\Framework\Asynchronous\Condition\Dependency;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Traits\Has_Name;
use ITRocks\Framework\View;
use ITRocks\Framework\Widget\Button;

/**
 * Asynchronous task request
 *
 * @business
 * @features
 * @set Asynchronous_Requests
 */
class Request
{
	use Has_Name;

	//-------------------------------------------------------------------------------------- FINISHED
	const FINISHED = 'finished';

	//----------------------------------------------------------------------------------- IN_PROGRESS
	const IN_PROGRESS = 'in_progress';

	//----------------------------------------------------------------------------------- $task_count
	/**
	 * Internal counter for distribute new tasks in all execution task
	 * @var integer
	 */
	private $task_count = 1;

	//------------------------------------------------------------------------------------- $creation
	/**
	 * @link DateTime
	 * @user readonly
	 * @var Date_Time
	 */
	public $creation;

	//--------------------------------------------------------------------------------------- $errors
	/**
	 * @calculated
	 * @getter
	 * @store false
	 * @user readonly
	 * @var Task[]
	 */
	public $errors;

	//------------------------------------------------------------------------------ $general_buttons
	/**
	 * @calculated
	 * @getter
	 * @store false
	 * @var Button[]
	 * @user invisible
	 */
	public $general_buttons;

	//--------------------------------------------------------------------------------- $max_progress
	/**
	 * @calculated
	 * @getter
	 * @store false
	 * @user readonly
	 * @var integer
	 */
	public $max_progress;

	//------------------------------------------------------------------------- $number_of_executions
	/**
	 * The number of process running to execute tasks
	 * @var integer
	 */
	public $number_of_executions = 1;

	//-------------------------------------------------------------------------------- $pending_tasks
	/**
	 * @calculated
	 * @getter
	 * @store false
	 * @user readonly
	 * @var Task[]
	 */
	public $pending_tasks;

	//------------------------------------------------------------------------------------- $progress
	/**
	 * @calculated
	 * @getter
	 * @store false
	 * @user readonly
	 * @var integer
	 */
	public $progress;

	//--------------------------------------------------------------------------------------- $status
	/**
	 * @calculated
	 * @getter
	 * @store false
	 * @user readonly
	 * @var string
	 */
	public $status;

	//---------------------------------------------------------------------------------------- $tasks
	/**
	 * Note :
	 * It not used for getProgress/add/etc because it's to slow when we have thousands of lines,
	 * and can be updated by other task pending execution
	 *
	 * @link Collection
	 * @user no_add, no_delete
	 * @var Task[]
	 */
	public $tasks;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Asynchronous request constructor.
	 *
	 * @param $label string
	 * @param $number_of_executions integer
	 */
	public function __construct($label = '', $number_of_executions = null)
	{
		if ($label) {
			$this->name = $label;
		}
		if ($number_of_executions) {
			$this->number_of_executions = $number_of_executions;
		}
	}

	//--------------------------------------------------------------------------------------- addTask
	/**
	 * @param $worker     Worker Worker of task
	 * @param $dependency Task If defined, new task wait the end of dependency task
	 * @return Task
	 */
	public function addTask(Worker $worker, Task $dependency = null)
	{
		/** @var $task Task */
		$task = Builder::create(static::getTaskClass());
		$task->worker = $worker;
		$task->request = $this;
		if ($dependency) {
			$task->group = $this->nextGroup();
		}
		if ($dependency) {
			$task->condition = new Dependency($dependency);
			// If has dependency, use the same group of execution
			// Not mandatory, but it's choice
			$task->group = $dependency->group;
		}
		else {
			$task->group = $this->nextGroup();
		}
		Dao::write($task);
		$this->checkRunningTaskLaunched($task);
		return $task;
	}

	//---------------------------------------------------------------------- checkRunningTaskLaunched
	/**
	 * @param $task Task
	 */
	private function checkRunningTaskLaunched($task)
	{
		if ($this->creation && !$this->creation->isEmpty() && $this->number_of_executions > 1) {
			$request = Running\Request::getRequest($task->request);
			if ($request) {
				/** @var $task Running\Task */
				$task = Dao::searchOne(
					['request' => $request, 'group' => $task->group], Running\Task::class
				);
				if ($task->status == Task::FINISHED) {
					$task->status = Task::PENDING;
					Dao::write($task);
					$task->asynchronousLaunch();
				}
			}
		}
	}

	//------------------------------------------------------------------------------------- getErrors
	/**
	 * @return Task[]
	 */
	public function getErrors()
	{
		if (isset($this->errors)) {
			return $this->errors;
		}
		$errors = Dao::search(['status' => Task::ERROR, 'request' => $this], static::getTaskClass());
		return $this->errors = ($errors ?: []);
	}

	//----------------------------------------------------------------------------- getGeneralButtons
	/**
	 * @return Button[]
	 */
	public function getGeneralButtons()
	{
		$buttons = [];
		if ($this->status == self::IN_PROGRESS) {
			$buttons[] = new Button('Recalculate', View::link($this, 'launch'), 'launch');
		}
		return $buttons;
	}

	//-------------------------------------------------------------------------------- getMaxProgress
	/**
	 * @return integer
	 */
	public function getMaxProgress()
	{
		return isset($this->max_progress) ?
			$this->max_progress :
			$this->max_progress = Dao::count(['request' => $this], static::getTaskClass());
	}

	//--------------------------------------------------------------------------------- getOutputLink
	/**
	 * @return string
	 */
	public function getOutputLink()
	{
		return View::link($this, 'output');
	}

	//------------------------------------------------------------------------------- getPendingTasks
	/**
	 * @return array|Task[]
	 */
	public function getPendingTasks()
	{
		if (isset($this->pending_tasks)) {
			return $this->pending_tasks;
		}
		$pending_tasks = Dao::search(
			['status' => Task::IN_PROGRESS, 'request' => $this], static::getTaskClass()
		);
		return $this->pending_tasks = ($pending_tasks ?: []);
	}

	//----------------------------------------------------------------------------------- getProgress
	/**
	 * @return integer
	 */
	public function getProgress()
	{
		return isset($this->progress) ?
			$this->progress :
			$this->progress = Dao::count(
				['status' => Task::FINISHED, 'request' => $this], static::getTaskClass()
			);
	}

	//------------------------------------------------------------------------------------- getStatus
	/**
	 * @return string
	 */
	public function getStatus()
	{
		$tasks_executed = $this->progress + count($this->errors);
		return $tasks_executed >= $this->max_progress ? static::FINISHED : static::IN_PROGRESS;
	}

	//---------------------------------------------------------------------------------- getTaskClass
	/**
	 * @return string
	 * @throws \Exception
	 */
	public static function getTaskClass()
	{
		return (new Reflection_Class(get_called_class()))->getProperty('tasks')
			->getType()->getElementTypeAsString();
	}

	//------------------------------------------------------------------------------ getTaskToExecute
	/**
	 * @param $group integer Group number for task.
	 * @return Task[]
	 */
	public function getTaskToExecute($group = 1)
	{
		/** @var $tasks Task[] */
		$tasks = Dao::search(
			['request' => $this, 'status' => Task::PENDING, 'group' => $group],
			static::getTaskClass()
		);
		return $tasks ?: [];
	}

	//------------------------------------------------------------------------------------- nextGroup
	/**
	 * @return integer
	 */
	private function nextGroup()
	{
		$this->task_count++;
		if ($this->task_count > $this->number_of_executions) {
			return $this->task_count = 1;
		}
		return $this->task_count;
	}

	//----------------------------------------------------------------------------------------- start
	/**
	 * Launch asynchronous task
	 */
	public function start()
	{
		$this->creation = new Date_Time();
		Dao::write($this);
		$this->launch();
	}

	//---------------------------------------------------------------------------------------- launch
	protected function launch()
	{
		$running_request = Running\Request::getRequest($this);
		if ($running_request) {
			$group_map = [];
			foreach ($running_request->tasks as $task) {
				// Re-launch all running tasks who are stopped
				if (in_array($task->status, [Task::PENDING, Task::FINISHED])) {
					$task->asynchronousLaunch();
					$group_map[$task->group] = true;
				}
			}
			for ($i = 1; $i <= $this->number_of_executions; $i++) {
				if (!isset($group_map[$i])) {
					$task = new Running\Task($i);
					$task->request = $running_request;
					Dao::write($task);
					$task->asynchronousLaunch();
				}
			}
		}
	}

}
