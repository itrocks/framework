<?php
namespace SAF\Framework\AOP\Joinpoint;

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
	public function __construct($class_name, $pointcut, $parameters, &$result, $advice)
	{
		$this->advice     = $advice;
		$this->class_name = $class_name;
		$this->parameters = $parameters;
		$this->pointcut   = $pointcut;
		$this->result     = &$result;
	}

}
