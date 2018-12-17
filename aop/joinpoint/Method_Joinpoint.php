<?php
namespace ITRocks\Framework\AOP\Joinpoint;

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
	 * @param $result      mixed
	 * @param $advice      string[]|object[]|string
	 */
	public function __construct($class_name, array $pointcut, $parameters, &$result, $advice)
	{
		parent::__construct($pointcut, $parameters, $result, $advice);
		$this->class_name  = $class_name;
		$this->method_name = $pointcut[1];
		$this->object      = is_object($pointcut[0]) ? $pointcut[0] : null;
	}

}
