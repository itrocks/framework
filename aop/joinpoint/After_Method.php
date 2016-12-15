<?php
namespace ITRocks\Framework\AOP\Joinpoint;

/**
 * After method joinpoint
 */
class After_Method extends Method_Joinpoint
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name  string
	 * @param $pointcut    string[]|object[]
	 * @param $parameters  array
	 * @param $result      mixed
	 * @param $advice      string[]|object[]|string
	 */
	public function __construct($class_name, array $pointcut, array $parameters, &$result, $advice)
	{
		parent::__construct($class_name, $pointcut, $parameters, $advice);
		$this->result     = &$result;
	}

}
