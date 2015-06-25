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

	//----------------------------------------------------------------------------------------- $stop
	/**
	 * The advice can set this to true to stop the calling process
	 * This tops everything, including other advices and original process call
	 *
	 * @var boolean
	 */
	public $stop = false;

}
