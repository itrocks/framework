<?php
namespace SAF\AOP;

/**
 * Function joinpoint
 */
abstract class Function_Joinpoint extends Joinpoint
{

	//------------------------------------------------------------------------------------ parameters
	/**
	 * @var array
	 */
	public $parameters;

	//--------------------------------------------------------------------------------------- $result
	/**
	 * If result is set and if the advice did not return any value (or null), this will be the
	 * pointcut result
	 *
	 * @var mixed
	 */
	public $result;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $pointcut    string
	 * @param $parameters  array
	 * @param $advice      string[]|object[]|string
	 */
	public function __construct($pointcut, $parameters, $advice)
	{
		$this->advice     = $advice;
		$this->parameters = $parameters;
		$this->pointcut   = $pointcut;
	}

}
