<?php
namespace SAF\Framework;

use SAF\Plugins;

/**
 * A very simple logger plugin that logs start and stop dates, pids and duration of main calls
 */
class Logger implements Plugins\Registerable
{

	//------------------------------------------------------------------------------------- $antiloop
	/**
	 * @var integer
	 */
	private $antiloop = 0;

	//------------------------------------------------------------------------------------ $log_entry
	/**
	 * @var Log_Entry
	 */
	private $log_entry;

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Plugins\Register
	 */
	public function register(Plugins\Register $register)
	{
		$aop = $register->aop;
		$aop->beforeMethod(
			array(Main_Controller::class, "runController"), array($this, "start")
		);
		$aop->afterMethod(
			array(Main_Controller::class, "runController"), array($this, "stop")
		);
	}

	//----------------------------------------------------------------------------------------- start
	/**
	 * Start logging : write pids and start-time
	 *
	 * @param $uri   string
	 * @param $get   array
	 * @param $post  array
	 * @param $files array
	 */
	public function start($uri, $get, $post, $files)
	{
		if (!$this->antiloop) {
			$this->log_entry = new Log_Entry($uri, $get, $post, $files);
			Dao::write($this->log_entry);
		}
		$this->antiloop ++;
	}

	//------------------------------------------------------------------------------------------ stop
	/**
	 * Stop logging : write end date-time and duration
	 */
	public function stop()
	{
		$this->antiloop--;
		if (!$this->antiloop) {
			$this->log_entry->stop();
			Dao::write($this->log_entry);
		}
	}

}
