<?php
namespace SAF\Framework;

class Ignore_Error_Handler implements Error_Handler
{

	//---------------------------------------------------------------------------------------- handle
	/**
	 * This error handler simply ignores those fucking errors and does nothing
	 *
	 * @param $error Handled_Error
	 */
	public function handle(Handled_Error $error)
	{
	}

}
