<?php
namespace SAF\Framework;

class Main_Error_Handler implements Error_Handler
{

	//---------------------------------------------------------------------------------------- handle
	/**
	 * This is the framework's main "simple" error handler : simple display of the error
	 *
	 * @param Handled_Error $error
	 */
	public function handle($error)
	{
		echo "<div class=\"Main_Error_Handler_handle\">"
			. $error->getErrorNumber() . " " . $error->getErrorMessage()
			. "<pre>" . print_r($error->getVariables(), true) . "</pre>"
			. "</div>\n";
	}

}
