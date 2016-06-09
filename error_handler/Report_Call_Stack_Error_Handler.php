<?php
namespace SAF\Framework\Error_Handler;

use SAF\Framework\Dao;
use SAF\Framework\Dao\Mysql\Link;
use SAF\Framework\Locale\Loc;
use SAF\Framework\Tools\Call_Stack;
use SAF\Framework\Tools\Call_Stack\Line;

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

	//---------------------------------------------------------------------------------------- handle
	/**
	 * @param $error Handled_Error
	 */
	public function handle(Handled_Error $error)
	{
		$code = new Error_Code($error->getErrorNumber());
		$stack = new Call_Stack();
		$message = '<div class="' . $code->caption() . ' handler">' . LF
			. '<span class="number">' . $code->caption() . '</span>' . LF
			. '<span class="message">' . $error->getErrorMessage() . '</span>' . LF
			. '<table class="call-stack">' . LF
				. $this->stackLinesTableRows($this->trace ?: $stack->lines())
			. '</table>' . LF
			. '</div>' . LF;
		if (ini_get('display_errors')) {
			echo $message . LF;
		}
		if (ini_get('log_errors') && ($log_file = ini_get('error_log'))) {
			$f = fopen($log_file, 'ab');
			$date = '[' . date('Y-m-d H:i:s') . ']' . SP;
			fputs($f, $date . ucfirst($code->caption()) . ':' . SP . $error->getErrorMessage() . LF);
			fputs($f, $this->processIdentification());
			fputs($f, $this->formData());
			fputs($f, $this->trace ?: $this->stackLinesText($stack->lines()));
			fclose($f);
		}
		if ($code->isFatal() || $this->trace) {
			echo '<div class="error">'
				. Loc::tr('An error occurred') . DOT
				. SP . Loc::tr('The software maintainer has been informed and will fix it soon') . DOT
				. SP . Loc::tr('Please check your data for bad input') . DOT
				. '</div>';
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
				$result[] = '<tr><td>' . ++$lines_count . '</td><td>' . $line . '</td><tr>';
			}
		}
		else {
			$result = [
				'<tr><th>#</th><th>class</th><th>method</th><th>file</th><th>line</th>'
			];
			foreach ($lines as $line) {
				$result[] = '<tr>'
					. '<td>' . ++$lines_count . '</td>'
					. '<td>' . $line->class . '</td>'
					. '<td>' . $line->function . '</td>'
					. '<td>' . $line->file . '</td>'
					. '<td>' . $line->line . '</td>'
					. '</tr>';
			}
		}
		return join(LF, $result);
	}

	//-------------------------------------------------------------------------------- stackLinesText
	/**
	 * @param $lines Line[]
	 * @return string
	 */
	private function stackLinesText($lines)
	{
		$lines_count = 0;
		$result = 'Stack trace:' . LF;
		foreach ($lines as $line) {
			$result .= '#' . ++$lines_count
				. SP . ($line->file ? ($line->file . SP) : '')
				. ($line->line ? ('(' . $line->line . '):') : '')
				. SP . ($line->class ? ($line->class . '->') : '') . $line->function . '()'
				. LF;
		}
		return $result;
	}

}
