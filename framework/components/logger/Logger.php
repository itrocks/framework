<?php
namespace SAF\Framework;

/**
 * A very simple logger plugin that logs start and stop dates, pids and duration of main calls
 */
class Logger implements Activable_Plugin
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

	//-------------------------------------------------------------------------------------- activate
	public function activate()
	{
		Aop::addBeforeMethodCall(
			array('SAF\Framework\Main_Controller', "runController"), array($this, "start")
		);
		Aop::addAfterMethodCall(
			array('SAF\Framework\Main_Controller', "runController"), array($this, "stop")
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

	//-------------------------------------------------------------------------------------- register
	/**
	 * Plugin registration : start before main controller call, stop after it's done.
	 *
	 * @param $register Plugin_Register
	 */
	public function register(Plugin_Register $register)
	{
	}

}
