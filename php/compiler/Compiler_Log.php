<?php
namespace ITRocks\Framework\PHP\Compiler;

use ITRocks\Framework\Logger\Entry;
use ITRocks\Framework\Reflection\Attribute\Class_\Set;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Tools\Date_Time;

/**
 * PHP Compiler log entry
 */
#[Set('Compiler_Log'), Store]
class Compiler_Log
{

	//----------------------------------------------------------------------------------- $class_name
	public string $class_name;

	//------------------------------------------------------------------------------------ $date_time
	public Date_Time|string $date_time;

	//------------------------------------------------------------------------------------------ $log
	public Entry $log;

}
