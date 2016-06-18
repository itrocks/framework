<?php
namespace SAF\Framework\Tools;

use Exception;
use SAF\Framework\Tools\Call_Stack\Line;
use SAF\Framework\View\Html\Template;

/**
 * Call stack class
 */
class Call_Stack
{

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
			$this->stack = $exception->getTrace();
		}
		else {
			$this->stack = debug_backtrace();
			array_shift($this->stack);
		}
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
	public function searchFunctions($functions)
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
	 */
	public function shift($count = 1)
	{
		while ($count-- > 0) {
			array_shift($this->stack);
		}
	}

}
