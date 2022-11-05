<?php
namespace ITRocks\Framework\Debug;

use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;

/**
 * An execution timer plugin, to enable the execution duration info on document's foot
 */
class Execution_Timer implements Registerable
{

	//----------------------------------------------------------------------------------- $start_time
	/**
	 * Program start time
	 *
	 * @var float
	 */
	private float $start_time;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Constructor : initializes start time to 'now'
	 */
	public function __construct()
	{
		$this->start_time = microtime(true);
	}

	//-------------------------------------------------------------- afterMainControllerRunController
	public function afterMainControllerRunController() : void
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
	public function begin() : void
	{
		$this->start_time = microtime(true);
	}

	//------------------------------------------------------------------------------------------- end
	/**
	 * Calculates and display execution duration
	 *
	 * @return float
	 */
	public function end() : float
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
	 * @param $register Register
	 */
	public function register(Register $register) : void
	{
		$aop = $register->aop;
		$aop->afterMethod([Main::class, 'runController'], [$this, 'afterMainControllerRunController']);
	}

}
