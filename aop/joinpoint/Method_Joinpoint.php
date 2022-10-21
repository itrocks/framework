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
	public string $class_name;

	//---------------------------------------------------------------------------------- $method_name
	/**
	 * @var string
	 */
	public string $method_name;

	//--------------------------------------------------------------------------------------- $object
	/**
	 * @var object
	 */
	public object $object;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @noinspection PhpMissingParentConstructorInspection $pointcut argument type does not match
	 * @param $class_name  string
	 * @param $pointcut    object[]|string[]
	 * @param $parameters  array
	 * @param $result      mixed
	 * @param $advice      object[]|string[]|string
	 */
	public function __construct(
		string $class_name, array $pointcut, array $parameters, mixed &$result, array|string $advice
	) {
		$this->advice      =  $advice;
		$this->class_name  =  $class_name;
		$this->method_name =  $pointcut[1];
		$this->object      =  is_object($pointcut[0]) ? $pointcut[0] : null;
		$this->parameters  =  $parameters;
		$this->pointcut    =  $pointcut;
		$this->result      =& $result;
	}

}
