<?php
namespace SAF\Framework;

/**
 * An interface for error handlers
 */
interface Error_Handler
{

	//---------------------------------------------------------------------------------------- handle
	/**
	 * @param $error Handled_Error
	 */
	public function handle(Handled_Error $error);

}
