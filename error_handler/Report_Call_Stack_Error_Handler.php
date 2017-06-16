<?php
namespace ITRocks\Framework\Error_Handler;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Mysql\Link;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Tools\Call_Stack;
use ITRocks\Framework\Tools\Call_Stack\Line;

/**
 * An error handler that reports the full call stack and not only the error message alone
 */
class Report_Call_Stack_Error_Handler implements Error_Handler
{

	//----------------------------------------------------------------------------------- $call_stack
	/**
	 * @var Call_Stack
	 */
	public $call_stack = null;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $call_stack Call_Stack
	 */
	public function __construct($call_stack = null)
	{
		if (isset($call_stack)) {
			$this->call_stack = $call_stack;
		}
	}

	//-------------------------------------------------------------------------------------- formData
	/**
	 * @return string
	 */
	private function formData()
	{
		$result = '_GET = ' . print_r($_GET, true);
		$result .= '_POST = ' . print_r($_POST, true);
		return $result;
	}

	//--------------------------------------------------------------------- getUserInformationMessage
	/**
	 * @return string
	 */
	static public function getUserInformationMessage()
	{
		return Loc::tr('An error occurred') . DOT
		. SP . Loc::tr('The software maintainer has been informed and will fix it soon') . DOT
		. SP . Loc::tr('Please check your data for bad input') . DOT;
	}

	//---------------------------------------------------------------------------------------- handle
	/**
	 * @param $error Handled_Error
	 */
	public function handle(Handled_Error $error)
	{
		if ($this->call_stack) {
			$reset_call_stack = false;
		}
		else {
			$this->call_stack = (new Call_Stack())->shift();
			$reset_call_stack = true;
		}
		$code = new Error_Code($error->getErrorNumber());
		if (ini_get('display_errors')) {
			$message = '<div class="' . $code->caption() . ' handler">' . LF
				. '<span class="number">' . $code->caption() . '</span>' . LF
				. '<span class="message">' . $error->getErrorMessage() . '</span>' . LF
				. '<table class="call-stack">' . LF
				. $this->call_stack->asHtml()
				. '</table>' . LF
				. '</div>' . LF;
			echo $message . LF;
		}

		$this->logError($error);

		if ($code->isFatal() || !$reset_call_stack) {
			echo '<div class="error">' . $this->getUserInformationMessage()	. '</div>';
		}

		if ($reset_call_stack) {
			$this->call_stack = null;
		}
	}

	//-------------------------------------------------------------------------------------- logError
	/**
	 * @param $error Handled_Error
	 */
	public function logError(Handled_Error $error)
	{
		$code = new Error_Code($error->getErrorNumber());
		if (ini_get('log_errors') && ($log_file = ini_get('error_log'))) {
			$f     = fopen($log_file, 'ab');
			$date  = '[' . date('Y-m-d H:i:s') . ']' . SP;
			fputs($f, $date . ucfirst($code->caption()) . ':' . SP . $error->getErrorMessage() . LF);
			fputs($f, (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'No REQUEST_URI') . LF);
			fputs($f, (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] . LF : ''));
			fputs($f, $this->processIdentification());
			fputs($f, $this->formData());
			fputs($f, $this->call_stack->asText());
			fputs($f, LF);
			fclose($f);
		}
	}

	//------------------------------------------------------------------------- processIdentification
	/**
	 * @return string
	 */
	private function processIdentification()
	{
		$result = 'PID = ' . posix_getpid();
		$link = Dao::current();
		if ($link instanceof Link) {
			/** $link Link */
			$result .= ' ; mysql-thread-id = ' . $link->getConnection()->thread_id;
		}
		$result .= ' ; ' . session_name() . ' = ' . session_id();
		return $result . LF;
	}

}
