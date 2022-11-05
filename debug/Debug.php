<?php
namespace ITRocks\Framework;

use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Tools\Call_Stack;

/**
 * Debug functions that are missing in standard
 */
abstract class Debug
{

	//--------------------------------------------------------------------------------------- display
	/**
	 * @param $text string
	 */
	public static function display(string $text) : void
	{
		echo date('Y-m-d H:i:s') . SP . $text . BR . LF;
	}

	//------------------------------------------------------------------------------------ globalDump
	/**
	 * Global variables dump : dump all variables and resource we can found :
	 * - $GLOBALS
	 * - $_SERVER
	 * - all static property values from classes
	 *
	 * I don't know where I could found those : help me if you can !
	 * - all static variables declared into functions
	 * - all opened resources (ie files or mysql links)
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $display boolean|string true or 'pre' if you want to displaying it
	 * @return array returns the result array
	 */
	public static function globalDump(bool|string $display = 'pre') : array
	{
		$dump['$GLOBALS'] = $GLOBALS;
		$dump['$_SERVER'] = $_SERVER;
		// call stack
		$call_stack = new Call_Stack();
		$call_stack->shift();
		$dump['CALL_STACK'] = objectToArray($call_stack->lines(), true);
		// static
		foreach (array_merge(get_declared_classes(), get_declared_traits()) as $class) {
			/** @noinspection PhpUnhandledExceptionInspection $class from call stack */
			foreach ((new Reflection_Class($class))->getProperties([T_EXTENDS, T_USE]) as $property) {
				if ($property->isStatic()) {
					/** @noinspection PhpUnhandledExceptionInspection accessible static property */
					$dump['STATIC'][$class][$property->name] = $property->getValue();
				}
			}
		}
		if ($display) {
			$pre = ($display === 'pre');
			echo ($pre ? '<pre>' : '') . print_r($dump, true) . ($pre ? '</pre>' : '');
		}
		return $dump;
	}

	//------------------------------------------------------------------------------------------- log
	/**
	 * @param $text string
	 */
	public static function log(string $text) : void
	{
		$f = fopen('debug.log', 'ab');
		fputs($f, date('Y-m-d H:i:s') . ' #' . getmypid() . SP . $text . LF);
		fclose($f);
	}

	//---------------------------------------------------------------------------------- logCallStack
	public static function logCallStack() : void
	{
		self::log('CALL STACK :');
		$f = fopen('debug.log', 'ab');
		foreach (debug_backtrace() as $key => $trace) {
			if (!isset($trace['file'])) {
				fputs($f,
					'>' . sprintf('%-3s', ($key + 1)) . SP
					. str_replace(LF, SP, print_r($trace, true)) . LF
				);
			}
			else {
				fputs($f,
					'>' . sprintf('%-3s', ($key + 1)) . SP
					. $trace['file'] . ':' . $trace['line'] . ' : ' . $trace['function'] . '()' . LF
				);
			}
		}
		fclose($f);
	}

	//-------------------------------------------------------------------------------------- whatGrew
	/**
	 * This tells what variable from the global dump has grown since the last call to whatGrew().
	 * Useful for memory leaks detection, but doubles the quantity of memory used
	 * The first call does nothing but initialize the global dump history
	 *
	 * @param $old  mixed the old value
	 * @param $new  mixed the new value
	 * @param $path string the value path @internal
	 * @return array returns an associative array of path and sizes (old, new)
	 */
	public static function whatGrew(mixed $old = null, mixed $new = null, string $path = '') : array
	{
		$result = [];
		if (!isset($old) && !isset($new)) {
			static $old_dump;
			$new_dump = self::globalDump(false);
			if (isset($old_dump)) {
				$result = self::whatGrew($old_dump, $new_dump);
			}
			$old_dump = $new_dump;
		}
		else {
			foreach ($old as $key => $old_value) if (isset($new[$key])) {
				$old_size = strlen(serialize($old_value));
				$new_size = strlen(serialize($new[$key]));
				if ($old_size < $new_size) {
					$sub_path = $path ? ($path . DOT . $key) : $key;
					$result = array_merge(
						[$sub_path => 'from ' . $old_size . ' to ' . $new_size],
						self::whatGrew($old_value, $new[$key], $sub_path)
					);
				}
			}
		}
		return $result;
	}

}
