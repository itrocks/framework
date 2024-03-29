<?php
namespace ITRocks\Framework\Error_Handler;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Mysql\Link;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Tools\Call_Stack;
use ITRocks\Framework\View\Json\Engine;

/**
 * An error handler that reports the full call stack and not only the error message alone
 */
class Report_Call_Stack_Error_Handler implements Error_Handler
{

	//---------------------------------------------------------------------------- "log as" constants
	const HTML = 'html';
	const TEXT = 'text';

	//---------------------------------------------------------------------------------------- STDOUT
	const STDOUT = 'php://stdout';

	//----------------------------------------------------------------------------------- $call_stack
	/**
	 * @var ?Call_Stack
	 */
	public ?Call_Stack $call_stack = null;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $call_stack Call_Stack|null
	 */
	public function __construct(Call_Stack $call_stack = null)
	{
		if (isset($call_stack)) {
			$this->call_stack = $call_stack;
		}
	}

	//---------------------------------------------------------------------------------- displayError
	/**
	 * Displays error, only if ini_get('display_errors') is set, into HTML or console output
	 *
	 * @param $error Handled_Error
	 */
	public function displayError(Handled_Error $error) : void
	{
		$code = new Error_Code($error->getErrorNumber());
		if (ini_get('display_errors')) {
			if ($_SERVER['REMOTE_ADDR'] === 'console') {
				$this->logError($error, self::STDOUT);
			}
			elseif (Engine::acceptJson()) {
				$this->logError($error);
			}
			else {
				echo LF . '<div class="' . htmlentities($code->caption()) . ' handler">' . LF;
				$this->logError($error, self::STDOUT, self::HTML);
				echo LF . '</div>' . LF;
			}
		}
	}

	//-------------------------------------------------------------------------------------- formData
	/**
	 * @return string
	 */
	private function formData() : string
	{
		$get  = $_GET;
		$post = $_POST;
		unsetKeyRecursive($get,  ['password', 'password2', 'user_password'], 'XXXX');
		unsetKeyRecursive($post, ['password', 'password2', 'user_password'], 'XXXX');
		$result = '_GET = '   . print_r($get, true);
		$result .= '_POST = ' . print_r($post, true);
		return $result;
	}

	//---------------------------------------------------------------------------------------- format
	/**
	 * @param $text string
	 * @param $as   string @values html, text
	 * @return string
	 */
	private function format(string $text, string $as) : string
	{
		return ($as === self::HTML) ? htmlentities($text) : $text;
	}

	//--------------------------------------------------------------------------- getDisplayedMessage
	/**
	 * Return displayed error message
	 *
	 * TODO HIGH $error should not exist, and why is this method used here only public ?
	 *
	 * @param $error Handled_Error
	 * @return string
	 */
	public function getDisplayedMessage(
		/** @noinspection PhpUnusedParameterInspection */ Handled_Error $error
	) : string
	{
		return ($_SERVER['REMOTE_ADDR'] === 'console')
			? static::getUserInformationMessage()
			: (
				'<!--target #query-->'
				. '<li class="error">'
				. static::getUserInformationMessage()
				. '</li>'
				. '<!--end-->');
	}

	//--------------------------------------------------------------------- getUserInformationMessage
	/**
	 * @return string
	 */
	static public function getUserInformationMessage() : string
	{
		if (http_response_code() === 500) {
			// makes sure the user information message will be displayed by the browser
			http_response_code(200);
		}
		return Loc::tr('An error occurred') . DOT
		. SP . Loc::tr('The software maintainer has been informed and will fix it soon') . DOT
		. SP . Loc::tr('Please check your data for bad input') . DOT;
	}

	//---------------------------------------------------------------------------------------- handle
	/**
	 * @param $error Handled_Error
	 */
	public function handle(Handled_Error $error) : void
	{
		if ($this->call_stack) {
			$reset_call_stack = false;
		}
		else {
			$this->call_stack = (new Call_Stack())->shift();
			$reset_call_stack = true;
		}
		$code = new Error_Code($error->getErrorNumber());

		$this->displayError($error);
		$this->logError($error);

		if ($code->isFatal() || !$reset_call_stack) {
			echo $this->getDisplayedMessage($error);
		}

		if ($reset_call_stack) {
			$this->call_stack = null;
		}
	}

	//-------------------------------------------------------------------------------------- logError
	/**
	 * @param $error    Handled_Error
	 * @param $log_file string|null
	 * @param $as       string @values html, text
	 */
	public function logError(Handled_Error $error, string $log_file = null, string $as = self::TEXT)
		: void
	{
		$code = new Error_Code($error->getErrorNumber());
		if (!$log_file) {
			$log_file = ini_get('log_errors') ? ini_get('error_log') : null;
		}
		if ($log_file) {
			$call_stack    = $this->call_stack ?: new Call_Stack();
			$code_caption  = ucfirst($this->format($code->caption(), $as));
			$date          = '[' . date('Y-m-d H:i:s') . ']';
			$error_message = $this->format($error->getErrorMessage(), $as);
			$f = ($log_file === self::STDOUT) ? null : fopen($log_file, 'ab');
			$lf            = ($as === self::HTML) ? BRLF : LF;
			$referer       = $_SERVER['HTTP_REFERER'] ?? '';
			$request_uri   = $_SERVER['REQUEST_URI']  ?? 'No REQUEST_URI';

			if ((Engine::acceptJson() && $f) || !Engine::acceptJson()) {
				$this->out($f, $date . SP . $code_caption . ':' . SP . $error_message . $lf);
				$this->out($f, $this->format($request_uri, $as) . $lf);
				$this->out($f, $referer ? ($this->format($referer, $as) . $lf) : '');
				$this->out($f, $this->processIdentification());
				$this->out($f, $this->format($this->formData(), $as));
				$this->out($f, ($as === self::HTML) ? $call_stack->asHtml() : $call_stack->asText());
			}

			if ($f) {
				fputs($f, $lf);
				fclose($f);
			}
		}
	}

	//------------------------------------------------------------------------------------------- out
	/**
	 * @param $f    ?resource If null : output
	 * @param $text string
	 */
	private function out(mixed $f, string $text) : void
	{
		if ($f) {
			fputs($f, $text);
		}
		else {
			echo $text;
		}
	}

	//------------------------------------------------------------------------- processIdentification
	/**
	 * @return string
	 */
	private function processIdentification() : string
	{
		$result = 'PID = ' . posix_getpid();
		$link   = Dao::current();
		if ($link instanceof Link) {
			/** $link Link */
			$result .= ' ; mysql-thread-id = ' . ($link->getConnection()->thread_id ?? '-');
		}
		$result .= ' ; ' . session_name() . ' = ' . session_id();
		return $result . LF;
	}

}
