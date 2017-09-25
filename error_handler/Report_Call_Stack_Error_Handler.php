<?php
namespace ITRocks\Framework\Error_Handler;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Mysql\Link;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Tools\Call_Stack;

/**
 * An error handler that reports the full call stack and not only the error message alone
 */
class Report_Call_Stack_Error_Handler implements Error_Handler
{

	//---------------------------------------------------------------------------- "log as" constants
	const HTML = 'html';
	const TEXT = 'text';

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
			if ($_SERVER['REMOTE_ADDR'] === 'console') {
				$this->logError($error, 'php://stdout');
			}
			else {
				echo LF . '<div class="' . htmlentities($code->caption()) . ' handler">' . LF;
				$this->logError($error, 'php://stdout', self::HTML);
				echo LF . '</div>' . LF;
			}
		}

		$this->logError($error);

		if ($code->isFatal() || !$reset_call_stack) {
			if ($_SERVER['REMOTE_ADDR'] === 'console') {
				echo $this->getUserInformationMessage();
			}
			else {
				echo '<div class="error">' . $this->getUserInformationMessage()	. '</div>';
			}
		}

		if ($reset_call_stack) {
			$this->call_stack = null;
		}
	}

	//-------------------------------------------------------------------------------------- logError
	/**
	 * @param $error    Handled_Error
	 * @param $log_file string
	 * @param $as       string @values html, text
	 */
	public function logError(Handled_Error $error, $log_file = null, $as = self::TEXT)
	{
		$code = new Error_Code($error->getErrorNumber());
		if (!$log_file) {
			$log_file = ini_get('log_errors') ? ini_get('error_log') : null;
		}
		if ($log_file) {
			$call_stack   = $this->call_stack ?: new Call_Stack();
			$code_caption = ($as === self::HTML) ? htmlentities($code->caption()) : $code->caption();
			$date         = '[' . date('Y-m-d H:i:s') . ']';
			$f            = fopen($log_file, 'ab');
			$lf           = ($as === self::HTML) ? BRLF : LF;
			fputs($f, $date . SP . ucfirst($code_caption) . ':' . SP . $error->getErrorMessage() . $lf);
			fputs($f, (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'No REQUEST_URI') . $lf);
			fputs($f, (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] . $lf : ''));
			fputs($f, $this->processIdentification());
			fputs($f, $this->formData());
			fputs($f, (($as === self::HTML) ? $call_stack->asHtml() : $call_stack->asText()));
			fputs($f, $lf);
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
