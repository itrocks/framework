<?php

class Handled_Error
{

	private $call_next_error_handlers = true;

	private $err_no;

	private $err_msg;

	private $filename;

	private $line_num;

	private $standard_php_error_handler_call = false;

	private $vars;

	//----------------------------------------------------------------------------------- __construct
	public function __construct($err_no, $err_msg, $filename, $line_num, $vars)
	{
		$this->erro_no = $err_no;
		$this->err_msg = $err_msg;
		$this->filename = $filename;
		$this->line_num = $line_num;
		$this->vars = $vars;
	}

	//-------------------------------------------------------------------- areNextErrorHandlersCalled
	public function areNextErrorHandlersCalled()
	{
		return $this->call_next_error_handlers;
	}

	//--------------------------------------------------------------------- dontCallNextErrorHandlers
	public function dontCallNextErrorHandlers()
	{
		$this->call_next_error_handlers = false;
	}

	//------------------------------------------------------------------- callStandardPhpErrorHandler
	public function callStandardPhpErrorHandler($call = true)
	{
		$this->standard_php_error_handler_call = $call;
	}

	//--------------------------------------------------------------- isStandardPhpErrorHandlerCalled
	public function isStandardPhpErrorHandlerCalled()
	{
		return $this->standard_php_error_handler_call;
	}

}
