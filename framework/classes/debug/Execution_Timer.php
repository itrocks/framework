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
		self::$start_time = microtime(true);
	}

	//------------------------------------------------------------------------------------------- end
	/**
	 * Calculates and display execution duration
	 */
	public static function end()
	{
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
		aop_add_after(
			__NAMESPACE__ . "\\Main_Controller->run()",
			array(__CLASS__, "end")
		);
	}

}
