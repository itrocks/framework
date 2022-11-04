<?php
namespace ITRocks\Framework\Tools\Call_Stack;

use Closure;
use ITRocks\Framework\Dao;

/**
 * Call stack line
 */
class Line
{

	//----------------------------------------------------------------------------------------- $args
	/**
	 * @var ?array
	 */
	private ?array $args = null;

	//------------------------------------------------------------------------------------ $arguments
	/**
	 * @var ?array
	 */
	public ?array $arguments = null;

	//---------------------------------------------------------------------------------------- $class
	/**
	 * @var string
	 */
	public string $class = '';

	//----------------------------------------------------------------------------------------- $file
	/**
	 * @var string
	 */
	public string $file = '';

	//------------------------------------------------------------------------------------- $function
	/**
	 * @var string
	 */
	public string $function = '';

	//----------------------------------------------------------------------------------------- $line
	/**
	 * @var integer
	 */
	public int $line = 0;

	//-------------------------------------------------------------------------- $max_argument_length
	/**
	 * Max length for an argument. set this to 0 for 'infinite'
	 *
	 * @var integer
	 */
	static public int $max_argument_length = 100;

	//--------------------------------------------------------------------------------------- $object
	/**
	 * @var ?object
	 */
	public ?object $object = null;

	//----------------------------------------------------------------------------------------- $type
	/**
	 * @var string
	 */
	public string $type = '';

	//------------------------------------------------------------------------------- argumentsAsText
	/**
	 * @return string
	 */
	public function argumentsAsText() : string
	{
		$arguments = [];
		foreach ($this->arguments as $argument) {
			$arguments[] = $this->dumpArgument(
				$argument, static::$max_argument_length, static::$max_argument_length
			);
		}
		return join(',', $arguments);
	}

	//---------------------------------------------------------------------------------- dumpArgument
	/**
	 * @param $argument         mixed
	 * @param $max_length       integer
	 * @param $max_array_length integer
	 * @return string
	 */
	protected function dumpArgument(mixed $argument, int $max_length, int $max_array_length) : string
	{
		if (is_object($argument)) {
			$identifier = ($argument instanceof Closure) ? null : Dao::getObjectIdentifier($argument);
			if (method_exists($argument, '__toString')) {
				$identifier = (isset($identifier) ? ($identifier . '=') : '') . $argument->__toString();
			}
			$dump = get_class($argument) . (isset($identifier) ? ('::' . $identifier) : '');
		}
		elseif (is_null($argument)) {
			$dump = 'null';
		}
		elseif ($argument === false) {
			$dump = 'false';
		}
		elseif ($argument === true) {
			$dump = 'true';
		}
		elseif (is_array($argument)) {
			$dump = $this->dumpArray($argument, $max_length, $max_array_length);
		}
		else {
			$dump = strval($argument);
		}
		if ($max_length && (strlen($dump) > $max_length)) {
			$dump = is_array($argument)
				? (substr($dump, 0, $max_length - 3) . '..]')
				: (substr($dump, 0, $max_length - 2) . '..');
		}
		return str_replace([CR, LF, TAB], ['\\r', '\\n', '\\t'], $dump);
	}

	//------------------------------------------------------------------------------------- dumpArray
	/**
	 * @param $array            array
	 * @param $max_length       integer
	 * @param $max_array_length integer
	 * @return string
	 */
	public function dumpArray(array $array, int $max_length, int $max_array_length) : string
	{
		$array_count = count($array);
		if (!$array_count) {
			return '[]';
		}
		$counter = 0;
		if ($array_count === 1) {
			$dump        = '[';
			$dump_length = 2;
		}
		else {
			$dump        = '[' . $array_count . ':';
			$dump_length = 3 + strlen($array_count);
		}
		foreach ($array as $key => $element) {
			if ($counter) {
				$dump .= ',';
				$dump_length ++;
			}
			if ($key !== $counter) {
				$counter = -1;
				$dump   .= $key . '=>';
				if ($key === $element) {
					$dump   .= '=';
					$element = '';
				}
			}
			elseif ($counter >= 0) {
				$counter ++;
			}
			$append = $this->dumpArgument(
				$element, $max_length, $max_array_length ? ($max_array_length - $dump_length) : 0
			);
			$dump_length += strlen($append);
			$dump        .= $append;
			if ($max_array_length && ($dump_length > $max_array_length)) {
				break;
			}
		}
		$dump .= ']';
		return $dump;
	}

	//----------------------------------------------------------------------- fromDebugBackTraceArray
	/**
	 * @param $debug_backtrace array
	 * @return Line
	 */
	public static function fromDebugBackTraceArray(array $debug_backtrace) : Line
	{
		$line = new Line();
		foreach ($debug_backtrace as $key => $value) {
			$line->$key = $value;
		}
		$line->arguments =& $line->args;
		if (!isset($line->arguments)) {
			$line->arguments = [];
		}
		return $line;
	}

}
