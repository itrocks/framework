<?php

interface Error_Handler
{

	//---------------------------------------------------------------------------------------- handle
	/**
	 * @param Handled_Error $handled_error
	 */
	public function handle($handled_error);

}
