<?php
namespace SAF\AOP;

/**
 * Around method joinpoint
 */
class Around_Method_Joinpoint extends Method_Joinpoint
{

	//------------------------------------------------------------------------------- $process_method
	/**
	 * @var string
	 */
	private $process_method;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name     string
	 * @param $pointcut       string[]|object[]
	 * @param $parameters     array
	 * @param $advice         string[]|object[]|string
	 * @param $process_method string
	 */
	public function __construct($class_name, $pointcut, $parameters, $advice, $process_method)
	{
		$this->advice         = $advice;
		$this->class_name     = $class_name;
		$this->parameters     = $parameters;
		$this->pointcut       = $pointcut;
		$this->process_method = $process_method;
	}

	//--------------------------------------------------------------------------------------- process
	/**
	 * Launch the method that which call was replaced by the advice
	 *
	 * @param $args mixed The arguments the original method was expected to receive
	 * @return mixed
	 */
	public function process($args = null)
	{
		if (Aop::DEBUG) {
			echo "process(" . print_r(func_get_args(), true) . "<br>";
		}
		return call_user_func_array(array($this->pointcut[0], $this->process_method), func_get_args());
	}

}
