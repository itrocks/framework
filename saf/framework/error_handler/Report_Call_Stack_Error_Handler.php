<?php
namespace SAF\Framework\Error_Handler;

use SAF\Framework\Tools\Call_Stack;
use SAF\Framework\Tools\Call_Stack\Line;

/**
 * An error handler that reports the full call stack and not only the error message alone
 */
class Report_Call_Stack_Error_Handler implements Error_Handler
{

	//---------------------------------------------------------------------------------------- handle
	/**
	 * @param $error Handled_Error
	 */
	public function handle(Handled_Error $error)
	{
		$code = new Error_Code($error->getErrorNumber());
		$stack = new Call_Stack();
		$stack->shift(2);
		$message = '<div class="' . $code->caption() . ' handler">'
			. '<span class="number">' . $code->caption() . '</span>'
			. '<span class="message">' . $error->getErrorMessage() . '</span>'
			. '<table class="call-stack">' . $this->stackLinesTableRows($stack->lines()) . '</table>'
			. '</div>' . LF;
		if (ini_get('display_errors')) {
			echo $message . LF;
		}
		if (ini_get('log_errors') && ($log_file = ini_get('error_log'))) {
			$f = fopen($log_file, 'ab');
			$date = '[' . date('Y-m-d H:i:s') . ']' . SP;
			fputs($f, $date . ucfirst($code->caption()) . ':' . SP . $error->getErrorMessage() . LF);
			fputs($f, $this->stackLinesText($stack->lines()));
			fclose($f);
		}
	}

	//--------------------------------------------------------------------------- stackLinesTableRows
	/**
	 * @param $lines Line[]
	 * @return string
	 */
	private function stackLinesTableRows($lines)
	{
		$lines_count = 0;
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
