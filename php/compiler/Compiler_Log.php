<?php
namespace ITRocks\Framework\PHP\Compiler;

use ITRocks\Framework\Logger\Entry;
use ITRocks\Framework\Tools\Date_Time;

/**
 * PHP Compiler log entry
 *
 * @business
 * @set Compiler_Log
 */
class Compiler_Log
{

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string
	 */
	public string $class_name;

	//------------------------------------------------------------------------------------ $date_time
	/**
	 * @link DateTime
	 * @var Date_Time|string
	 */
	public Date_Time|string $date_time;

	//------------------------------------------------------------------------------------------ $log
	/**
	 * @link Object
	 * @var Entry
	 */
	public Entry $log;

}
