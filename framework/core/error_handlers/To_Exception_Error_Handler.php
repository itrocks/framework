<?php
namespace SAF\Framework;

/**
 * An error handler that transforms an error to an exception
 */
class To_Exception_Error_Handler implements Error_Handler
{

	//---------------------------------------------------------------------------------------- handle
	/**
	 * Change error to an exception
	 *
	 * @param $error           Handled_Error
	 * @param $exception_class string an exception class name
	 * @throws $exception_class
	 */
	public function handle(Handled_Error $error, $exception_class = '\Error_Exception')
	{
		throw new $exception_class(
			$error->getErrorMessage(),
			$error->getErrorNumber(),
			0,
			$error->getFilename(),
			$error->getLineNumber()
		);
	}

}
