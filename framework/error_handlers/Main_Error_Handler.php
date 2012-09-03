<?php

class Main_Error_Handler implements Error_Handler
{

	//---------------------------------------------------------------------------------------- handle
	/**
	 * @param Handled_Error $handled_error
	 */
	public function handle($handled_error)
	{
		echo "<div class=\"Main_Error_Handler_handle\">"
			. $handled_error->getErrorNumber() . " " . $handled_error->getErrorMessage()
			. "</div>\n";
	}

}
