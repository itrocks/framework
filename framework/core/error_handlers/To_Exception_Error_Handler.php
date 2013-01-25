<?php
namespace SAF\Framework;
use ErrorException;

class To_Exception_Error_Handler implements Error_Handler
{

	//---------------------------------------------------------------------------------------- handle
	/**
	 * Change error to an exception
	 *
	 * @param $error Handled_Error
	 * @throws ErrorException
	 */
	public function handle(Handled_Error $error)
	{
		throw new ErrorException(
			$error->getErrorMessage(),
			$error->getErrorNumber(),
			0,
			$error->getFilename(),
			$error->getLineNumber()
		);
	}

}
