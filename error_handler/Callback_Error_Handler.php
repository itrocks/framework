<?php
namespace ITRocks\Framework\Error_Handler;

/**
 * An error handler with a callback function
 */
class Callback_Error_Handler implements Error_Handler
{

	//---------------------------------------------------------------------------- $callback_function
	/**
	 * @var callable
	 */
	private $callback_function;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $callback_function callable the callback function
	 *                           (a function name or a class name and function name array)
	 */
	public function __construct(callable $callback_function)
	{
		$this->callback_function = $callback_function;
	}

	//---------------------------------------------------------------------------------------- handle
	/**
	 * Call error handled callback function using handled error object
	 *
	 * @param $handled_error Handled_Error
	 */
	public function handle(Handled_Error $handled_error)
	{
		call_user_func($this->callback_function, $handled_error);
	}

}
