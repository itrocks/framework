<?php

class Function_Autoloader_Error_Handler extends Error_Handler
{

	//----------------------------------------------------------------------------------- __construct
	public function __construct($callback_function)
	{
		parent::__construct(array($this, "handlerCallBack"));
	}
	
	//-------------------------------------------------------------------------------- handleCallBack
	/**
	 * @param Handled_Error $handled_error
	 */
	public function handlerCallBack($handled_error)
	{
		echo "<pre>handle " . print_r($handled_error, true) . "</pre>";
		$handled_error->dontCallNextErrorHandlers();
	}

}
