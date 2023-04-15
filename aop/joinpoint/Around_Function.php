<?php
namespace ITRocks\Framework\AOP\Joinpoint;

/**
 * Around function joinpoint
 */
class Around_Function extends Function_Joinpoint
{

	//----------------------------------------------------------------------------- $process_function
	private string $process_function;

	//----------------------------------------------------------------------------------- __construct
	/** @param $advice string[]|object[]|string */
	public function __construct(
		string $pointcut, array $parameters, mixed &$result, array|string $advice,
		string $process_function
	) {
		parent::__construct($pointcut, $parameters, $result, $advice);
		$this->process_function = $process_function;
	}

	//--------------------------------------------------------------------------------------- process
	/**
	 * Launch the function that which call was replaced by the advice
	 *
	 * @param $args mixed The arguments the original function was expected to receive
	 */
	public function process(mixed $args = null) : mixed
	{
		return call_user_func_array($this->process_function, func_get_args());
	}

}
