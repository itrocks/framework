<?php
namespace SAF\Framework;

class Callback_Error_Handler implements Error_Handler
{

	//---------------------------------------------------------------------------- $callback_function
	/**
	 * @var ambigous<string,multitype:ambigous<string,object>> "functionName", "Class_Name::functionName", array("Class_Name", "functionName"), array($object, "functionName")
	 */
	private $callback_function;

	//----------------------------------------------------------------------------------- __construct
	public function __construct($callback_function)
	{
		$this->callback_function = $callback_function;
	}

	//---------------------------------------------------------------------------------------- handle
	/**
	 * Call error handled callback function using handled error object
	 *
	 * @param Handled_Error $handled_error
	 */
	public function handle($handled_error)
	{
		call_user_func($this->callback_function, $handled_error);
	}

}
