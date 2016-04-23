<?php
namespace SAF\Framework\Tools\Call_Stack;

/**
 * Call stack line
 */
class Line
{

	//----------------------------------------------------------------------------------------- $args
	/**
	 * @var array
	 */
	private $args;

	//------------------------------------------------------------------------------------ $arguments
	/**
	 * @var array
	 */
	public $arguments;

	//---------------------------------------------------------------------------------------- $class
	/**
	 * @var string
	 */
	public $class;

	//----------------------------------------------------------------------------------------- $file
	/**
	 * @var string
	 */
	public $file;

	//------------------------------------------------------------------------------------- $function
	/**
	 * @var string
	 */
	public $function;

	//----------------------------------------------------------------------------------------- $line
	/**
	 * @var integer
	 */
	public $line;

	//--------------------------------------------------------------------------------------- $object
	/**
	 * @var object
	 */
	public $object;

	//----------------------------------------------------------------------------------------- $type
	/**
	 * @var string
	 */
	public $type;

	//----------------------------------------------------------------------- fromDebugBackTraceArray
	/**
	 * @param $debug_backtrace array
	 * @return Line
	 */
	public static function fromDebugBackTraceArray($debug_backtrace)
	{
		$line = new Line();
		foreach ($debug_backtrace as $key => $value) {
			$line->$key = $value;
		}
		$line->arguments =& $line->args;
		return $line;
	}

}
