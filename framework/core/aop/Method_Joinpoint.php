<?php
namespace SAF\Framework;

require_once "Function_Joinpoint.php";

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

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name  string
	 * @param $pointcut    string[]|object[]
	 * @param $parameters  array
	 * @param $advice      string[]|object[]|string
	 */
	public function __construct($class_name, $pointcut, $parameters, $advice)
	{
		$this->advice     = $advice;
		$this->class_name = $class_name;
		$this->parameters = $parameters;
		$this->pointcut   = $pointcut;
	}

}
