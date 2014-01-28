<?php
namespace SAF\Framework;

/**
 * Around function joinpoint
 */
class Around_Function_Joinpoint
{

	//--------------------------------------------------------------------------------------- $advice
	/**
	 * @var string[]|object[]|string
	 */
	public $advice;

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string
	 */
	public $function_name;

	//----------------------------------------------------------------------------- $process_callback
	/**
	 * @var string[]|object[]
	 */
	private $process_callback;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $function_name    string
	 * @param $advice           string[]|object[]|string
	 * @param $process_callback array
	 */
	public function __construct($function_name, $advice, $process_callback)
	{
		$this->function_name    = $function_name;
		$this->advice           = $advice;
		$this->process_callback = $process_callback;
	}

	//-------------------------------------------------------------------------------------- $process
	/**
	 * Launch the method that which call was replaced by the advice
	 *
	 * @param $args mixed The arguments the original method was expected to receive
	 * @return mixed
	 */
	public function process($args = null)
	{
		return call_user_func_array($this->process_callback, func_get_args());
	}

}
