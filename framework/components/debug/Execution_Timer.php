<?php
namespace SAF\Framework;

/** @noinspection PhpIncludeInspection called from index.php */
require_once "framework/core/toolbox/Aop.php";

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
		Aop::add("after",
			__NAMESPACE__ . "\\Main_Controller->run()",
			array(__CLASS__, "end")
		);
	}

}
