<?php
namespace ITRocks\Framework\Error_Handler;

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
	public function handle(Handled_Error $error) : void
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
