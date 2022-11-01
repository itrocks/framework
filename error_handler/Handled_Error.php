<?php
namespace ITRocks\Framework\Error_Handler;

use ITRocks\Framework\Debug;

/**
 * An error that error handlers can handle
 */
class Handled_Error
{

	//--------------------------------------------------------------------- $call_next_error_handlers
	/**
	 * Will next error handlers be called ?
	 * Default behaviour is to call all error handlers.
	 *
	 * @var boolean
	 */
	private bool $call_next_error_handlers = true;

	//-------------------------------------------------------------------------------------- $err_msg
	/**
	 * The error message
	 *
	 * @var string
	 */
	private string $err_msg;

	//--------------------------------------------------------------------------------------- $err_no
	/**
	 * Level of the error raised
	 *
	 * @var integer
	 */
	private int $err_no;

	//------------------------------------------------------------------------------------- $filename
	/**
	 * The filename that the error was raised in
	 *
	 * @var string
	 */
	private string $filename;

	//------------------------------------------------------------------------------------- $line_num
	/**
	 * The line number the error was raised at
	 *
	 * @var integer
	 */
	private int $line_num;

	//-------------------------------------------------------------- $standard_php_error_handler_call
	/**
	 * Will standard php error handler be called ?
	 * Default behaviour is NOT to call the php error handler (for handled error numbers of course)
	 *
	 * @var boolean
	 */
	private bool $standard_php_error_handler_call = false;

	//----------------------------------------------------------------------------------------- $vars
	/**
	 * Variables that existed in the scope the error was triggered in.
	 * User error handler must not modify error context.
	 *
	 * @var array
	 */
	private array $vars;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Handled error construction is done by Error_Handlers::handle() and should not be done manually
	 *
	 * @param $err_no   integer php error number
	 * @param $err_msg  string  php error message
	 * @param $filename string  php script filename where the error occurs
	 * @param $line_num integer line number into the php script file where the error occurs
	 * @param $vars     array   error context : all active variables and their values when the error
	 *                  occurred
	 */
	public function __construct(
		int $err_no, string $err_msg, string $filename, int $line_num, array $vars = []
	) {
		$this->err_no   = $err_no;
		$this->err_msg  = $err_msg;
		$this->filename = $filename;
		$this->line_num = $line_num;
		$this->vars     = $vars;
	}

	//-------------------------------------------------------------------- areNextErrorHandlersCalled
	/**
	 * Return true if next error handlers should be called
	 *
	 * Default is true, will be false if dontCallNextErrorHandlers() has been called for this error
	 *
	 * @return boolean
	 */
	public function areNextErrorHandlersCalled() : bool
	{
		return $this->call_next_error_handlers;
	}

	//------------------------------------------------------------------- callStandardPhpErrorHandler
	/**
	 * For this error, standard php error handler will be called (or not if false)
	 *
	 * Default behaviour is to don't call php error handler for handled errors.
	 *
	 * @param $call boolean set this to false if you don't want php error handler to be called anymore
	 * @return $this
	 */
	public function callStandardPhpErrorHandler(bool $call = true) : static
	{
		$this->standard_php_error_handler_call = $call;
		return $this;
	}

	//--------------------------------------------------------------------- dontCallNextErrorHandlers
	/**
	 * For this error, next error handlers will not be called
	 *
	 * An error handler will call this if it wants other error handlers not to be called after it.
	 * Default behaviour is to call all error handlers.
	 *
	 * @return $this
	 */
	public function dontCallNextErrorHandlers() : static
	{
		$this->call_next_error_handlers = false;
		return $this;
	}

	//------------------------------------------------------------------------------- getErrorMessage
	/**
	 * Gets handled error message
	 *
	 * @return string
	 */
	public function getErrorMessage() : string
	{
		return $this->err_msg;
	}

	//-------------------------------------------------------------------------------- getErrorNumber
	/**
	 * Gets the level of the error raised
	 *
	 * @return integer
	 */
	public function getErrorNumber() : int
	{
		return $this->err_no;
	}

	//----------------------------------------------------------------------------------- getFilename
	/**
	 * Gets the filename that the error was raised in
	 *
	 * @return string
	 */
	public function getFilename() : string
	{
		return $this->filename;
	}

	//--------------------------------------------------------------------------------- getLineNumber
	/**
	 * Gets the line number the error was raised at
	 *
	 * @return integer
	 */
	public function getLineNumber() : int
	{
		return $this->line_num;
	}

	//---------------------------------------------------------------------------- getUserErrorNumber
	/**
	 * Convert error number as a user error number that can be triggered
	 *
	 * @return integer
	 */
	public function getUserErrorNumber() : int
	{
		switch ($this->err_no) {
			case E_DEPRECATED:
				return E_USER_DEPRECATED;
			case E_COMPILE_ERROR:
			case E_CORE_ERROR:
			case E_PARSE:
			case E_RECOVERABLE_ERROR:
			case E_STRICT:
			case E_ERROR:
				return E_USER_ERROR;
			case E_NOTICE:
				return E_USER_NOTICE;
			case E_COMPILE_WARNING:
			case E_CORE_WARNING:
			case E_WARNING:
				return E_USER_WARNING;
		}
		return E_USER_ERROR;
	}

	//---------------------------------------------------------------------------------- getVariables
	/**
	 * Gets the variables active when the error occurred
	 *
	 * @return array
	 */
	public function getVariables() : array
	{
		return Debug::globalDump(false);
	}

	//--------------------------------------------------------------------------------------- getVars
	/**
	 * Gets the variables that existed in the scope the error was triggered in
	 *
	 * @return array
	 */
	public function getVars() : array
	{
		return $this->vars;
	}

	//--------------------------------------------------------------- isStandardPhpErrorHandlerCalled
	/**
	 * Returns true if standard php error handler will be called
	 *
	 * This is to true if callStandardPhpErrorHandler(true) has been called in the error handler or a previous one.
	 *
	 * @return boolean
	 */
	public function isStandardPhpErrorHandlerCalled() : bool
	{
		return $this->standard_php_error_handler_call;
	}

}
