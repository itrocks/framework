<?php
namespace SAF\Framework\PHP\Compiler;

use SAF\Framework\Logger\Entry;
use SAF\Framework\Tools\Date_Time;

/**
 * PHP Compiler log entry
 *
 * @set Compiler_Log
 */
class Compiler_Log
{

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string
	 */
	public $class_name;

	//------------------------------------------------------------------------------------ $date_time
	/**
	 * @link DateTime
	 * @var Date_Time
	 */
	public $date_time;

	//--
	/**
	 * @link Object
	 * @var Entry
	 */
	public $log;

}
