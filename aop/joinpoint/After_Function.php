<?php
namespace ITRocks\Framework\AOP\Joinpoint;

/**
 * After function joinpoint
 */
class After_Function extends Function_Joinpoint
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $pointcut   string
	 * @param $parameters array
	 * @param $result     mixed
	 * @param $advice     string[]|object[]|string
	 */
	public function __construct($pointcut, array $parameters, &$result, $advice)
	{
		parent::__construct($pointcut, $parameters, $advice);
		$this->result = &$result;
	}

}
