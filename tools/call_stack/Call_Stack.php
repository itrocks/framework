<?php
namespace ITRocks\Framework\Tools;

use Exception;
use ITRocks\Framework\Tools\Call_Stack\Line;
use ITRocks\Framework\View\Html\Template;

/**
 * Call stack class
 */
class Call_Stack
{

	//--------------------------------------------------------------------------------- $is_exception
	/**
	 * @var boolean
	 */
	public $is_exception = false;

	//---------------------------------------------------------------------------------------- $stack
	/**
	 * Raw call stack array, given by debug_backtrace()
	 * Each element is an array with 'args', 'class', 'file', 'function', 'line' elements keys
	 *
	 * @var array
	 */
	private $stack;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Constructs a call stack analyzer object, for a given exception or from current call stack
	 *
	 * @param $exception Exception
	 */
	public function __construct(Exception $exception = null)
	{
		if ($exception) {
			$this->is_exception = true;
			$this->stack        = $exception->getTrace();
		}
		else {
			$this->stack = debug_backtrace();
			array_shift($this->stack);
		}
	}

	//---------------------------------------------------------------------------------------- asHtml
	/**
	 * @return string
	 */
	public function asHtml()
	{
		$lines_count = 0;
		$result      = [
			'<table>',
			'<tr><th>#</th><th>class</th><th>method</th><th>arguments</th></tg><th>file</th><th>line</th>'
		];
		foreach ($this->lines() as $line) {
			$result_line ='<tr><td>' . ++$lines_count . '</td>';
			$line_data = [
				$line->class,
				$line->function,
				$line->argumentsAsText(),
				$line->file,
				$line->line
			];
			foreach ($line_data as $data) {
				$result_line .= '<td>' . htmlentities($data, ENT_QUOTES|ENT_HTML5) . '</td>';
			}
			$result_line .= '</tr>';
			$result[] = $result_line;
		}
		$result[] = '</table>';
		return join(LF, $result) . LF;
	}

	//---------------------------------------------------------------------------------------- asText
	/**
	 * @return string
	 */
	public function asText()
	{
		$lines_count = 0;
		$result      = ($this->is_exception ? 'Exception' : 'Error') . ' stack trace:' . LF;
		foreach ($this->lines() as $line) {
			$line_object = $line->object
				? substr($line->dumpArray(get_object_vars($line->object), 100, 100), 1, -1)
				: '';
			if ($line_object) {
				$line_object = '{' . $line_object . '}';
			}
			$result .= '#' . ++$lines_count
				. ($line->file ? (SP . $line->file . ':' . ($line->line ? ($line->line . ':') : '')) : '')
				. SP . (($line->class || $line_object) ? ($line->class . $line_object . '->') : '')
				. $line->function
				. '(' . $line->argumentsAsText() . ')'
				. LF;
		}
		return $result;
	}

	//--------------------------------------------------------------------------------- containsClass
	/**
	 * Returns true if the call stack contains a call to the given class
	 *
	 * @param $class_name string
	 * @return boolean
	 */
	public function containsClass($class_name)
	{
		foreach ($this->stack as $stack) {
			if (isset($stack['class']) && ($stack['class'] === $class_name)) {
				return true;
			}
		}
		return false;
	}

	//------------------------------------------------------------------------------------ getFeature
	/**
	 * Get current call feature from call stack
	 *
	 * @return string|null
	 */
	public function getFeature()
	{
		/** @var $template Template */
		if ($template = $this->getObject(Template::class)) {
			return $template->getFeature();
		}
		return null;
	}

	//------------------------------------------------------------------------------------- getObject
	/**
	 * Get top object that is an instance of $class_name from the call stack
	 *
	 * @param $class_name string Can be a the name of a class, interface or trait
	 * @return object|null
	 */
	public function getObject($class_name)
	{
		foreach ($this->stack as $stack) {
			if (isset($stack['object']) && isA($stack['object'], $class_name)) {
				return $stack['object'];
			}
		}
		return null;
	}

	//----------------------------------------------------------------------------------------- lines
	/**
	 * @return Line[]
	 */
	public function lines()
	{
		$lines = [];
		foreach ($this->stack as $line) {
			$lines[] = Line::fromDebugBackTraceArray($line);
		}
		return $lines;
	}

	//------------------------------------------------------------------------------- searchFunctions
	/**
	 * Returns true if the call stack contains any of the given functions
	 *
	 * @param $functions string[] The searched functions
	 * @return Line|null The first matching line if found, else false
	 */
	public function searchFunctions(array $functions)
	{
		foreach ($this->stack as $stack) {
			if (
				isset($stack['function'])
				&& !isset($stack['class'])
				&& in_array($stack['function'], $functions)
			) {
				return Line::fromDebugBackTraceArray($stack);
			}
		}
		return null;
	}

	//----------------------------------------------------------------------------------------- shift
	/**
	 * @param $count integer
	 * @return static
	 */
	public function shift($count = 1)
	{
		while ($count-- > 0) {
			array_shift($this->stack);
		}
		return $this;
	}

}
