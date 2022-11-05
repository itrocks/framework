<?php
namespace ITRocks\Framework\Error_Handler;

/**
 * An error handler that simply ignores errors (no side effects)
 */
class Ignore_Error_Handler implements Error_Handler
{

	//---------------------------------------------------------------------------------------- handle
	/**
	 * This error handler simply ignores those fucking errors and does nothing
	 *
	 * @param $error Handled_Error
	 */
	public function handle(Handled_Error $error) : void
	{
	}

}
