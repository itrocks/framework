<?php
namespace ITRocks\Framework\Error_Handler;

use JetBrains\PhpStorm\NoReturn;

/**
 * Fatal error handler : this handler display the error and stops the program
 */
class Fatal_Error_Handler extends Main_Error_Handler
{

	//---------------------------------------------------------------------------------------- handle
	/**
	 * This is the framework's main 'simple' error handler : simple display of the error
	 *
	 * @param $error Handled_Error
	 */
	#[NoReturn]
	public function handle(Handled_Error $error)
	{
		die(
			'<div class="fatal error handler">'
			. '<span class="number">' . $error->getErrorNumber() . '</span>'
			. '<p>' . $error->getErrorMessage() . '</p>'
			. '<pre>' . print_r($error->getVariables(), true) . '</pre>'
			. '</div>' . LF
		);
	}

}
