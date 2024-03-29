<?php
namespace ITRocks\Framework\Tools;

use Exception;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Reflection\Reflection_Function;
use ITRocks\Framework\Reflection\Reflection_Method;
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
	public bool $is_exception = false;

	//---------------------------------------------------------------------------------------- $stack
	/**
	 * Raw call stack array, given by debug_backtrace()
	 * Each element is an array with 'args', 'class', 'file', 'function', 'line' elements keys
	 *
	 * @var array
	 */
	private array $stack;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Constructs a call stack analyzer object, for a given exception or from current call stack
	 *
	 * @param $exception Exception|null
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
	public function asHtml() : string
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
				$result_line .= '<td>' . htmlentities(strval($data), ENT_QUOTES|ENT_HTML5) . '</td>';
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
	public function asText() : string
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

	//------------------------------------------------------------------------- calledMethodArguments
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $method    array
	 * @param $arguments array key is the argument number or name (slower)
	 * @return ?Line
	 */
	public function calledMethodArguments(array $method, array $arguments) : ?Line
	{
		foreach ($arguments as $argument_name => $argument) {
			if (is_string($argument_name)) {
				if (!isset($reflection_method)) {
					/** @noinspection PhpUnhandledExceptionInspection must be a valid method*/
					$reflection_method = new Reflection_Method(reset($method), end($method));
				}
				$parameter       = $reflection_method->getParameters()[$argument_name];
				$argument_number = $parameter->getPosition();
				unset($arguments[$argument_name]);
				$arguments[$argument_number] = $argument;
			}
		}
		foreach ($this->stack as $stack) {
			if ($this->methodMatches($stack, $method)) {
				$found = true;
				foreach ($arguments as $argument_number => $argument) {
					if (is_object($argument) && Dao::getObjectIdentifier($argument)) {
						if (!Dao::is($argument, $stack['args'][$argument_number])) {
							$found = false;
							break;
						}
					}
					elseif ($argument !== $stack['args'][$argument_number]) {
						$found = false;
						break;
					}
				}
				if ($found) {
					return Line::fromDebugBackTraceArray($stack);
				}
			}
		}
		return null;
	}

	//--------------------------------------------------------------------------------- containsClass
	/**
	 * Returns true if the call stack contains a call to the given class
	 *
	 * @param $class_name string
	 * @return boolean
	 */
	public function containsClass(string $class_name) : bool
	{
		foreach ($this->stack as $stack) {
			if (isset($stack['class']) && ($stack['class'] === $class_name)) {
				return true;
			}
		}
		return false;
	}

	//-------------------------------------------------------------------------------- containsMethod
	/**
	 * Get top matching method Line
	 *
	 * @param $method array if object, must be exactly the same instance
	 * @return boolean
	 */
	public function containsMethod(array $method) : bool
	{
		foreach ($this->stack as $stack) {
			if ($this->methodMatches($stack, $method)) {
				return true;
			}
		}
		return false;
	}

	//----------------------------------------------------------------------------- containsNamespace
	/**
	 * Returns true if the call stack contains a call to any class under the given namespace
	 *
	 * @param $namespace string
	 * @return boolean
	 */
	public function containsNamespace(string $namespace) : bool
	{
		$length = strlen($namespace);
		foreach ($this->stack as $stack) {
			if (isset($stack['class']) && substr($stack['class'], 0, $length) === $namespace) {
				return true;
			}
		}
		return false;
	}

	//-------------------------------------------------------------------------------- containsObject
	/**
	 * Returns true if the call stack contains the object, or an instance of a class
	 *
	 * containsClass will return the class where the call comes from, not the read class the object
	 * was declared. containsObject tests the real final class of the object, or allow to search the
	 * instance itself.
	 *
	 * @param $object object|string object or class name
	 * @return boolean
	 */
	public function containsObject(object|string $object) : bool
	{
		if (is_string($object)) {
			foreach ($this->stack as $stack) {
				if (isset($stack['object']) && (isA($stack['object'], $object))) {
					return true;
				}
			}
		}
		else {
			foreach ($this->stack as $stack) {
				if (isset($stack['object']) && ($stack['object'] === $object)) {
					return true;
				}
			}
		}
		return false;
	}

	//------------------------------------------------------------------------------ getArgumentValue
	/**
	 * Returns the value of a function / method parameter that matches the name
	 *
	 * This use reflection to get the argument names : so beware, this may be slow !
	 *
	 * @param $argument_name string
	 * @param $non_empty     boolean if true, jump to the first function with non-empty value
	 * @return mixed
	 */
	public function getArgumentValue(string $argument_name, bool $non_empty = false) : mixed
	{
		return $this->getArgumentsValue([$argument_name], $non_empty);
	}

	//----------------------------------------------------------------------------- getArgumentsValue
	/**
	 * Returns the value of a function / method parameter that matches any of argument names
	 *
	 * This use reflection to get the argument names : so beware, this may be slow !
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $argument_names string[]
	 * @param $non_empty      boolean if true, jump to the first function with non-empty value
	 * @return mixed
	 */
	public function getArgumentsValue(array $argument_names, bool $non_empty = false) : mixed
	{
		foreach ($this->stack as $stack) {
			$function = null;
			if (!empty($stack['class']) && !empty($stack['function'])) {
				if (method_exists($stack['class'], $stack['function'])) {
					/** @noinspection PhpUnhandledExceptionInspection call stack is valid */
					$function = new Reflection_Method($stack['class'], $stack['function']);
				}
			}
			elseif (!empty($stack['function'])) {
				if (function_exists($stack['function'])) {
					/** @noinspection PhpUnhandledExceptionInspection call stack is valid */
					$function = new Reflection_Function($stack['function']);
				}
			}
			if (!$function) {
				continue;
			}
			foreach ($argument_names as $argument_name) {
				if ($function->hasParameter($argument_name)) {
					$arguments = $function->getParametersNames(false);
					$argument  = array_search($argument_name, $arguments);
					if (
						($non_empty && !empty($stack['args'][$argument]))
						|| (!$non_empty && isset($stack['args'][$argument]))
					) {
						return $stack['args'][$argument];
					}
				}
			}
		}
		return null;
	}

	//------------------------------------------------------------------------------------ getFeature
	/**
	 * Get current call feature from call stack
	 *
	 * @return ?string
	 */
	public function getFeature() : ?string
	{
		if ($template = $this->getObject(Template::class)) {
			return $template->getFeature();
		}
		return null;
	}

	//------------------------------------------------------------------------------------- getMethod
	/**
	 * Get top matching method Line
	 *
	 * @param $method array if object, must be exactly the same instance
	 * @return ?Line
	 */
	public function getMethod(array $method) : ?Line
	{
		foreach ($this->stack as $stack) {
			if ($this->methodMatches($stack, $method)) {
				return Line::fromDebugBackTraceArray($stack);
			}
		}
		return null;
	}

	//----------------------------------------------------------------------------- getMethodArgument
	/**
	 * Get top matching method argument value
	 *
	 * @param $method          array if object, must be exactly the same instance
	 * @param $argument_number integer argument number (0..n)
	 * @return mixed null if not found or value was null
	 */
	public function getMethodArgument(array $method, int $argument_number = 0) : mixed
	{
		foreach ($this->stack as $stack) {
			if ($this->methodMatches($stack, $method)) {
				return $stack['args'][$argument_number];
			}
		}
		return null;
	}

	//------------------------------------------------------------------------------------- getObject
	/**
	 * Get top object that is an instance of $class_name from the call stack
	 *
	 * @param $class_name class-string<T> Can be a the name of a class, interface or trait
	 * @return ?T
	 * @template T
	 */
	public function getObject(string $class_name) : ?object
	{
		foreach ($this->stack as $stack) {
			if (isset($stack['object']) && isA($stack['object'], $class_name)) {
				return $stack['object'];
			}
		}
		return null;
	}

	//----------------------------------------------------------------------------- getObjectArgument
	/**
	 * Get top object that is the value of an argument of $class_name
	 *
	 * @param $class_name class-string<T> Can be the name of a class, interface or trait
	 * @return ?T
	 * @template T
	 */
	public function getObjectArgument(string $class_name) : ?object
	{
		foreach ($this->stack as $stack) {
			if (isset($stack['args'])) {
				foreach ($stack['args'] as $argument) {
					if (is_object($argument) && isA($argument, $class_name)) {
						return $argument;
					}
				}
			}
		}
		return null;
	}

	//----------------------------------------------------------------------------------------- lines
	/**
	 * @return Line[]
	 */
	public function lines() : array
	{
		$lines = [];
		foreach ($this->stack as $line) {
			$lines[] = Line::fromDebugBackTraceArray($line);
		}
		return $lines;
	}

	//----------------------------------------------------------------------------------- methodCount
	/**
	 * Get method call count
	 *
	 * @param $method array if object, must be exactly the same instance
	 * @return integer
	 */
	public function methodCount(array $method) : int
	{
		$method_count = 0;
		foreach ($this->stack as $stack) {
			if ($this->methodMatches($stack, $method)) {
				$method_count ++;
			}
		}
		return $method_count;
	}

	//--------------------------------------------------------------------------------- methodMatches
	/**
	 * @param $stack  array Call stack entry
	 * @param $method array
	 * @return boolean
	 */
	protected function methodMatches(array $stack, array $method) : bool
	{
		return isset($stack['args'])
			&& isset($stack['function']) && ($stack['function'] === $method[1])
			&& (
				(
					isset($stack['object'])
					&& (
						(is_object($method[0]) && ($stack['object'] === $method[0]))
						|| (is_string($method[0]) && isA($stack['object'], $method[0]))
					)
				)
				|| (
					isset($stack['class'])
					&& is_string($method[0])
					&& isA($method[0], $stack['class'])
				)
			);
	}

	//------------------------------------------------------------------------------------------- pop
	/**
	 * @return $this
	 */
	public function pop() : static
	{
		array_shift($this->stack);
		return $this;
	}

	//------------------------------------------------------------------------------- searchFunctions
	/**
	 * Returns true if the call stack contains any of the given functions
	 *
	 * @param $functions string[] The searched functions
	 * @return ?Line The first matching line if found, else false
	 */
	public function searchFunctions(array $functions) : ?Line
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
	 * @return $this
	 */
	public function shift(int $count = 1) : static
	{
		while ($count-- > 0) {
			array_shift($this->stack);
		}
		return $this;
	}

}
