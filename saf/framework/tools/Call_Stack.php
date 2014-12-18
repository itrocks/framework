<?php
namespace SAF\Framework\Tools;

use Exception;
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
	 * Get current call feature from callstack
	 *
	 * @return string|null
	 */
	public function getFeature()
	{
		foreach ($this->stack as $stack) {
			if (isset($stack['object']) && is_a($stack['object'], Template::class)) {
				/** @var $template Template */
				$template = $stack['object'];
				$feature = $template->getFeature();
				return $feature;
			}
		}
		return null;
	}

}
