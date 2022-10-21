<?php
namespace ITRocks\Framework\AOP\Joinpoint;

use ITRocks\Framework\AOP\Joinpoint;

/**
 * Function joinpoint
 */
abstract class Function_Joinpoint extends Joinpoint
{

	//----------------------------------------------------------------------------------- $parameters
	/**
	 * @var array
	 */
	public array $parameters;

	//--------------------------------------------------------------------------------------- $result
	/**
	 * If result is set and if the advice did not return any value (or null), this will be the
	 * pointcut result
	 *
	 * @var mixed
	 */
	public mixed $result;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $pointcut    string
	 * @param $parameters  array
	 * @param $result      mixed
	 * @param $advice      object[]|string[]|string
	 */
	public function __construct(
		string $pointcut, array $parameters, mixed &$result, array|string $advice
	) {
		$this->advice     =  $advice;
		$this->parameters =  $parameters;
		$this->pointcut   =  $pointcut;
		$this->result     =& $result;
	}

}
