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
		$this->err_no   = $err_no;
		$this->err_msg  = $err_msg;
		$this->filename = $filename;
		$this->line_num = $line_num;
		$this->vars     = $vars;
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

	//------------------------------------------------------------------------------- getErrorMessage
	public function getErrorMessage()
	{
		return $this->err_msg;
	}

	//-------------------------------------------------------------------------------- getErrorNumber
	public function getErrorNumber()
	{
		return $this->err_no;
	}

	//----------------------------------------------------------------------------------- getFilename
	public function getFilename()
	{
		return $this->filename;
	}

	//--------------------------------------------------------------------------------- getLineNumber
	public function getLineNumber()
	{
		return $this->line_num;
	}

	//--------------------------------------------------------------- isStandardPhpErrorHandlerCalled
	public function isStandardPhpErrorHandlerCalled()
	{
		return $this->standard_php_error_handler_call;
	}

}
