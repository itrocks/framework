<?php
namespace SAF\AOP;

/**
 * After function joinpoint
 */
class After_Function_Joinpoint extends Function_Joinpoint
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $pointcut   string
	 * @param $parameters array
	 * @param $result     mixed
	 * @param $advice     string[]|object[]|string
	 */
	public function __construct($pointcut, $parameters, &$result, $advice)
	{
		$this->advice     = $advice;
		$this->parameters = $parameters;
		$this->pointcut   = $pointcut;
		$this->result     = &$result;
	}

}
