<?php
namespace ITRocks\Framework\AOP\Joinpoint;

/**
 * Around function joinpoint
 */
class Around_Function extends Function_Joinpoint
{

	//----------------------------------------------------------------------------- $process_function
	/**
	 * @var string
	 */
	private $process_function;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $pointcut         string
	 * @param $parameters       array
	 * @param $result           mixed
	 * @param $advice           string[]|object[]|string
	 * @param $process_function string
	 */
	public function __construct($pointcut, array $parameters, &$result, $advice, $process_function)
	{
		parent::__construct($pointcut, $parameters, $result, $advice);
		$this->process_function = $process_function;
	}

	//--------------------------------------------------------------------------------------- process
	/**
	 * Launch the function that which call was replaced by the advice
	 *
	 * @param $args mixed The arguments the original function was expected to receive
	 * @return mixed
	 */
	public function process($args = null)
	{
		return call_user_func_array($this->process_function, func_get_args());
	}

}
