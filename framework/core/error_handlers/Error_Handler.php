<?php
namespace SAF\Framework;

interface Error_Handler
{

	//---------------------------------------------------------------------------------------- handle
	/**
	 * @param $error Handled_Error
	 */
	public function handle(Handled_Error $error);

}
