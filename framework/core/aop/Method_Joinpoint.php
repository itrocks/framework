<?php
namespace SAF\Framework;

/**
 * Method joinpoint
 */
abstract class Method_Joinpoint extends Function_Joinpoint
{

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string
	 */
	public $class_name;

	//---------------------------------------------------------------------------------- $method_name
	/**
	 * @var string
	 */
	public $method_name;

	//--------------------------------------------------------------------------------------- $object
	/**
	 * @var object
	 */
	public $object;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name  string
	 * @param $pointcut    string[]|object[]
	 * @param $parameters  array
	 * @param $advice      string[]|object[]|string
	 */
	public function __construct($class_name, $pointcut, $parameters, $advice)
	{
		$this->advice      = $advice;
		$this->class_name  = $class_name;
		$this->method_name = $pointcut[1];
		$this->object      = is_object($pointcut[0]) ? $pointcut[0] : null;
		$this->parameters  = $parameters;
		$this->pointcut    = $pointcut;
	}

}
