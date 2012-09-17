<?php
namespace SAF\Framework;

require_once "framework/classes/toolbox/Aop.php";

abstract class Execution_Timer
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
		Execution_Timer::$start_time = microtime(true);
	}

	//------------------------------------------------------------------------------------------- end
	/**
	 * Calculates and display execution duration
	 */
	public static function end()
	{
		$duration = number_format(microtime(true) - Execution_Timer::$start_time, 3, ",", " ");
		echo "<script type=\"text/javascript\">"
			. " document.getElementById(\"main_page\").innerHTML"
			. " += '<div class=\"Timer logger duration\">$duration</div>';"
			. " </script>";
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * Reset execution timer counter and register to timer end and result display
	 */
	public static function register()
	{
		Execution_Timer::begin();
		Aop::registerAfter(
			__NAMESPACE__ . "\\Main_Controller->run()",
			array(__CLASS__, "end")
		);
	}

}

Execution_Timer::register();
