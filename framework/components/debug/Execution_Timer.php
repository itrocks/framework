<?php
namespace SAF\Framework;

/** @noinspection PhpIncludeInspection */
require_once "framework/core/aop/Aop.php";

/**
 * An execution timer plugin, to enable the execution duration info on document's foot
 */
class Execution_Timer implements Plugin
{

	/**
	 * Program start time
	 *
	 * @var float
	 */
	private $start_time;

	//----------------------------------------------------------------------------------- __construct
	public function __construct()
	{
		$this->start_time = microtime(true);
	}

	//----------------------------------------------------------------------------------------- begin
	/**
	 * Record actual microtime as beginnig of the timer
	 */
	public function begin()
	{
		$this->start_time = microtime(true);
	}

	//------------------------------------------------------------------------------------------- end
	/**
	 * Calculates and display execution duration
	 *
	 * @return float
	 */
	public function end()
	{
		if (!isset($this->start_time)) {
			$this->start_time = $_SERVER["REQUEST_TIME_FLOAT"];
		}
		return (microtime(true) - $this->start_time);
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * Reset execution timer counter and register to timer end and result display
	 */
	public static function register()
	{
		$timer = new Execution_Timer();
		Aop::add(
			Aop::AFTER, 'SAF\Framework\Main_Controller->runController()',
			function() use($timer) {
				$duration = number_format($timer->end(), 3, ",", " ");
				echo "<script type=\"text/javascript\">"
					. " document.getElementById(\"main\").innerHTML"
					. " += '<div class=\"Timer logger duration\">$duration</div>';"
					. " </script>";
			}
		);
	}

}
