<?php
namespace SAF\Framework;

/** @noinspection PhpIncludeInspection */
require_once "framework/core/toolbox/Aop.php";

/**
 * An execution timer plugin, to enable the execution duration info on document's foot
 */
abstract class Execution_Timer implements Plugin
{

	/**
	 * Program start time
	 *
	 * @var float
	 */
	private static $start_time;

	//----------------------------------------------------------------------------------------- begin
	/**
	 * Record actual microtime as beginnig of the timer
	 */
	public static function begin()
	{
		self::$start_time = microtime(true);
	}

	//------------------------------------------------------------------------------------------- end
	/**
	 * Calculates and display execution duration
	 */
	public static function end()
	{
		if (!isset(self::$start_time)) {
			self::$start_time = $_SERVER["REQUEST_TIME_FLOAT"];
		}
		$duration = number_format(microtime(true) - self::$start_time, 3, ",", " ");
		echo "<script type=\"text/javascript\">"
			. " document.getElementById(\"main\").innerHTML"
			. " += '<div class=\"Timer logger duration\">$duration</div>';"
			. " </script>";
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * Reset execution timer counter and register to timer end and result display
	 */
	public static function register()
	{
		self::begin();
		Aop::add(Aop::AFTER, 'SAF\Framework\Main_Controller->runController()', array(__CLASS__, "end"));
	}

}
