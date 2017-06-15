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

	//---------------------------------------------------------------------------------------- $trace
	/**
	 * @var string
	 */
	public $trace = null;

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
		$code = new Error_Code($error->getErrorNumber());
		if (ini_get('display_errors')) {
			$stack = $this->trace ?: new Call_Stack();
			$message = '<div class="' . $code->caption() . ' handler">' . LF
				. '<span class="number">' . $code->caption() . '</span>' . LF
				. '<span class="message">' . $error->getErrorMessage() . '</span>' . LF
				. '<table class="call-stack">' . LF
				. $this->stackLinesTableRows($this->trace ?: $stack->lines())
				. '</table>' . LF
				. '</div>' . LF;
			echo $message . LF;
		}

		$this->logError($error);

		if ($code->isFatal() || $this->trace) {
			echo '<div class="error">' . $this->getUserInformationMessage()	. '</div>';
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
			$stack = $this->trace ?: new Call_Stack();
			$f = fopen($log_file, 'ab');
			$date = '[' . date('Y-m-d H:i:s') . ']' . SP;
			fputs($f, $date . ucfirst($code->caption()) . ':' . SP . $error->getErrorMessage() . LF);
			fputs($f, (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'No REQUEST_URI') . LF);
			fputs($f, (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] . LF : ''));
			fputs($f, $this->processIdentification());
			fputs($f, $this->formData());
			fputs($f, $this->trace ?: $this->stackLinesText($stack->lines()));
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

	//---------------------------------------------------------------------------- stackLineArguments
	/**
	 * @param $line Line
	 * @return string
	 */
	private function stackLineArguments(Line $line)
	{
		$arguments = [];
		foreach ($line->arguments as $argument) {
			if (is_object($argument)) {
				$identifier = Dao::getObjectIdentifier($argument);
				if (method_exists($argument, '__toString')) {
					$identifier = (isset($identifier) ? ($identifier . '=') : '') . strval($argument);
				}
				$argument = get_class($argument) . (isset($identifier) ? ('::' . $identifier) : '');
			}
			elseif (is_null($argument)) {
				$argument = 'null';
			}
			elseif ($argument === false) {
				$argument = 'false';
			}
			elseif ($argument === true) {
				$argument = 'true';
			}
			elseif (is_array($argument)) {
				$argument = 'array:' . (count($argument) ?: '-');
			}
			else {
				$argument = strval($argument);
			}
			$arguments[] = $argument;
		}
		return join(', ', $arguments);
	}

	//--------------------------------------------------------------------------- stackLinesTableRows
	/**
	 * @param $lines Line[]|string
	 * @return string
	 */
	private function stackLinesTableRows($lines)
	{
		$lines_count = 0;
		if (is_string($lines)) {
			$result = [];
			foreach (explode(LF, $lines) as $line) {
				$result[] = '<tr>'
					. '<td>' . ++$lines_count . '</td>'
					. '<td>' . htmlentities($line, ENT_QUOTES|ENT_HTML5) . '</td>'
					. '<tr>';
			}
		}
		else {
			$result = [
				'<tr><th>#</th><th>class</th><th>method</th><th>arguments</th></tg><th>file</th><th>line</th>'
			];
			foreach ($lines as $line) {
				$result_line ='<tr><td>' . ++$lines_count . '</td>';
				$line_data = [
					$line->class,
					$line->function,
					$this->stackLineArguments($line),
					$line->file,
					$line->line
				];
				foreach ($line_data as $data) {
					$result_line .= '<td>' . htmlentities($data, ENT_QUOTES|ENT_HTML5) . '</td>';
				}
				$result_line .= '</tr>';
				$result[] = $result_line;
			}
		}
		return join(LF, $result);
	}

	//-------------------------------------------------------------------------------- stackLinesText
	/**
	 * @param $lines Line[]
	 * @return string
	 */
	private function stackLinesText(array $lines)
	{
		$lines_count = 0;
		$result = 'Stack trace:' . LF;
		foreach ($lines as $line) {
			$result .= '#' . ++$lines_count
				. SP . ($line->file ? ($line->file . SP) : '')
				. ($line->line ? ('(' . $line->line . '):') : '')
				. SP . ($line->class ? ($line->class . '->') : '') . $line->function
				. '(' . $this->stackLineArguments($line) . ')'
				. LF;
		}
		return $result;
	}

}
