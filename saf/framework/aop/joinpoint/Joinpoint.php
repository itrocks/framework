<?php
namespace SAF\Framework\AOP;

/**
 * The base joinpoint object
 */
abstract class Joinpoint
{

	//--------------------------------------------------------------------------------------- $advice
	/**
	 * @var callable
	 */
	public $advice;

	//------------------------------------------------------------------------------------- $pointcut
	/**
	 * @var callable
	 */
	public $pointcut;

}
