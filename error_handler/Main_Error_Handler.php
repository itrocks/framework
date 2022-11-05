<?php
namespace ITRocks\Framework\Error_Handler;

/**
 * The main error handler displays the error and resume
 */
class Main_Error_Handler implements Error_Handler
{

	//---------------------------------------------------------------------------------------- handle
	/**
	 * This is the framework's main 'simple' error handler : simple display of the error
	 *
	 * @param $error Handled_Error
	 */
	public function handle(Handled_Error $error) : void
	{
		echo '<div class="fatal error handler">'
		. '<span class="number">' . $error->getErrorNumber() . '</span>'
		. '<p>' . $error->getErrorMessage() . '</p>'
		. '<pre>' . print_r($error->getVariables(), true) . '</pre>'
		. '</div>' . LF;
	}

}
