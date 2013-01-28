<?php
namespace SAF\Framework;
use AopJoinpoint;
use ErrorException;

class Class_Debugger implements Plugin
{

	private static $depth = 1;

	//----------------------------------------------------------------------------------- __construct
	private function __construct() {}

	//----------------------------------------------------------------------------------------- after
	/**
	 * Advice called after each joinpoint method
	 *
	 * @param $joinpoint AopJoinpoint
	 */
	public static function after(AopJoinpoint $joinpoint)
	{
		self::$depth--;
		echo "<div class='debug'>"
		. str_repeat("&lt; ", self::$depth) . "END "
		. $joinpoint->getClassName() . "::" . $joinpoint->getMethodName() . "("
		. Class_Debugger::outputArguments($joinpoint->getArguments()) . ")"
		. " -> " . $joinpoint->getReturnedValue()
		. "</div>";
	}

	//---------------------------------------------------------------------------------------- before
	/**
	 * Advice called before each joinpoint method
	 *
	 * @param $joinpoint AopJoinpoint
	 */
	public static function before(AopJoinpoint $joinpoint)
	{
		echo "<div class='debug'>"
		. str_repeat("&gt; ", self::$depth) . "CALL "
		. $joinpoint->getClassName() . "::" . $joinpoint->getMethodName() . "("
		. Class_Debugger::outputArguments($joinpoint->getArguments()) . ")"
		. "</div>";
		self::$depth ++;
	}

	//------------------------------------------------------------------------------- outputArguments
	/**
	 * Convert arguments list into an html output string
	 *
	 * @param $arguments mixed[]
	 * @return string
	 */
	private static function outputArguments($arguments)
	{
		$result = "";
		foreach ($arguments as $argument) {
			$result .= ", ";
			if (is_object($argument)) {
				$result .= get_class($argument);
			}
			try {
				$result .= (is_object($argument) ? " = " : "")
					. (is_array($argument) ? "array" . count($argument) : $argument);
			} catch (ErrorException $e) {
				$result .= (is_object($argument) ? " = " : "") . gettype($argument);
			}
		}
		return substr($result, 2);
	}

	//----------------------------------------------------------------------------------- getInstance
	/**
	 * Get the Class_Debugger instance
	 *
	 * @return Class_Debugger
	 */
	public static function getInstance()
	{
		static $instance = null;
		if (!isset($instance)) {
			$instance = new Class_Debugger();
		}
		return $instance;
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		self::registerClass("*");
	}

	//--------------------------------------------------------------------------------- registerClass
	public static function registerClass($class_name)
	{
		if ($class_name == "*") {
			Aop::add("before", "*->*()", array(get_called_class(), "before"));
			Aop::add("after", "*->*()", array(get_called_class(), "after"));
		}
		else {
			Aop::add("before",
				Namespaces::fullClassName($class_name) . "->*()", array(get_called_class(), "before")
			);
			Aop::add("after",
				Namespaces::fullClassName($class_name) . "->*()", array(get_called_class(), "after")
			);
		}
	}

}
