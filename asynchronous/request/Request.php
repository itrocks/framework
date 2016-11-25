<?php
namespace ITRocks\Framework\Asynchronous;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Tools\Paths;
use ITRocks\Framework\Traits\Has_Name;
use ITRocks\Framework\View;

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

	//-------------------------------------------------------------------------------- $creation_date
	/**
	 * @link DateTime
	 * @var Date_Time
	 */
	public $creation;

	//---------------------------------------------------------------------------------------- $tasks
	/**
	 * Note :
	 * It not used for getProgress/add/etc because it's to slow when we have thousands of lines,
	 * and can be updated by other task pending execution
	 *
	 * @link Collection
	 * @var Task[]
	 */
	public $tasks;

	//------------------------------------------------------------------------------------- $progress
	/**
	 * @calculated
	 * @store false
	 * @getter
	 * @var integer
	 */
	public $progress;

	//--------------------------------------------------------------------------------- $max_progress
	/**
	 * @calculated
	 * @store false
	 * @getter
	 * @var integer
	 */
	public $max_progress;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Asynchronous constructor.
	 * @param $label string
	 */
	public function __construct($label = '')
	{
		if ($label) {
			$this->name = $label;
		}
	}

	//--------------------------------------------------------------------------------------- addTask
	/**
	 * @param $worker Worker
	 */
	public function addTask(Worker $worker)
	{
		/** @var $task Task */
		$task = Builder::create(static::getTaskClass());
		$task->worker = $worker;
		$task->request = $this;
		Dao::write($task);
	}

	//----------------------------------------------------------------------------------------- start
	/**
	 * Launch asynchronous task
	 */
	public function start()
	{
		$this->creation = new Date_Time();
		Dao::write($this);
		$this->asynchronousLaunch();
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

	//---------------------------------------------------------------------------- asynchronousLaunch
	public function asynchronousLaunch()
	{
		$host = $_SERVER['HTTP_HOST'];
		$controller_url = $host . Paths::$uri_root . Paths::$script_name
			. View::link($this, 'execute');
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $controller_url);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, false);
		curl_setopt($curl, CURLOPT_TIMEOUT_MS, 100);
		curl_exec($curl);
		curl_close($curl);
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

}
