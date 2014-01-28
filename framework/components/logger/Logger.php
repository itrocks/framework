<?php
namespace SAF\Framework;

use AopJoinpoint;

/**
 * A very simple logger plugin that logs start and stop dates, pids and duration of main calls
 */
class Logger implements Plugin
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

	//----------------------------------------------------------------------------------------- start
	/**
	 * Start logging : write pids and start-time
	 *
	 * @param $joinpoint AopJoinpoint
	 */
	public function start(AopJoinpoint $joinpoint)
	{
		// the controller may not receive $get, $post or $files arguments
		@list($uri, $get, $post, $files) = $joinpoint->getArguments();
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
	 */
	public static function register()
	{
		$logger = new Logger();
		Aop::addBeforeMethodCall(
			array('SAF\Framework\Main_Controller', "runController"), array($logger, "start")
		);
		Aop::addAfterMethodCall(
			array('SAF\Framework\Main_Controller', "runController"), array($logger, "stop")
		);
	}

}
