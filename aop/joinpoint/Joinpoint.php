<?php
namespace ITRocks\Framework\AOP;

/**
 * The base joinpoint object
 */
abstract class Joinpoint
{

	//--------------------------------------------------------------------------------------- $advice
	/**
	 * @noinspection PhpDocFieldTypeMismatchInspection callable is stored as array|string
	 * @var callable
	 */
	public array|string $advice;

	//------------------------------------------------------------------------------------- $pointcut
	/**
	 * @noinspection PhpDocFieldTypeMismatchInspection callable is stored as array|string
	 * @var callable
	 */
	public array|string $pointcut;

	//----------------------------------------------------------------------------------------- $stop
	/**
	 * The advice can set this to true to stop the calling process
	 * This stops everything, including other advices and original process call
	 *
	 * @var boolean
	 */
	public bool $stop = false;

}
