<?php
namespace SAF\Framework\Debug;

use SAF\Framework\Controller\Main;
use SAF\Plugins;

/**
 * An execution timer plugin, to enable the execution duration info on document's foot
 */
class Execution_Timer implements Plugins\Registerable
{

	//----------------------------------------------------------------------------------- $start_time
	/**
	 * Program start time
	 *
	 * @var float
	 */
	private $start_time;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Constructor : initializes start time to 'now'
	 */
	public function __construct()
	{
		$this->start_time = microtime(true);
	}

	//-------------------------------------------------------------- afterMainControllerRunController
	public function afterMainControllerRunController()
	{
		$duration = number_format($this->end(), 3, ',', SP);
		echo '<script type="text/javascript">'
			. ' document.getElementById("main").innerHTML'
			. ' += \'<div class="Timer logger duration">' . $duration . '</div>\';'
			. ' </script>';
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
			$this->start_time = $_SERVER['REQUEST_TIME_FLOAT'];
		}
		return (microtime(true) - $this->start_time);
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * Reset execution timer counter and register to timer end and result display
	 *
	 * @param $register Plugins\Register
	 */
	public function register(Plugins\Register $register)
	{
		$aop = $register->aop;
		$aop->afterMethod([Main::class, 'runController'], [$this, 'afterMainControllerRunController']);
	}

}
