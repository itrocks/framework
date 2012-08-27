<?php

class Error_Handler
{

	/**
	 * @var mixed "functionName", array("className", "functionName"), array($object, "functionName")
	 */
	private $callback_function;

	//----------------------------------------------------------------------------------- __construct
	public function __construct($callback_function)
	{
		$this->callback_function = $callback_function;
	}

	//---------------------------------------------------------------------------------------- handle
	/**
	 * call error handled callback function using handled error object
	 *
	 * @param Handled_Error $handled_error
	 */
	public function handle($handled_error)
	{
		call_user_func($this->callback_function, $handled_error);
	}

}
