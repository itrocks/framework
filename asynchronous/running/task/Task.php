<?php
namespace ITRocks\Framework\Asynchronous\Running;

use ITRocks\Framework\Asynchronous;
use ITRocks\Framework\Tools\Paths;
use ITRocks\Framework\View;

/**
 * @set Asynchronous_Running_Tasks
 */
class Task extends Asynchronous\Task
{
	//-------------------------------------------------------------------------------------- $request
	/**
	 * @link Object
	 * @composite
	 * @var Request
	 */
	public $request;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Task constructor.
	 * @param $task_number integer
	 */
	public function __construct($task_number = null)
	{
		if ($task_number) {
			$this->group = $task_number;
		}
		if (!$this->worker) {
			$this->worker = new Worker();
		}
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

}
