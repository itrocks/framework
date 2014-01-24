<?php
namespace SAF\Framework;

chdir("..");
include "framework/core/reflection/Reflection_Argument.php";
include "framework/core/reflection/Reflection_Method.php";

abstract class Aop
{

	//----------------------------------------------------------------------------------------- AFTER
	const AFTER = "AFTER";

	//---------------------------------------------------------------------------------------- AROUND
	const AROUND = "AROUND";

	//---------------------------------------------------------------------------------------- BEFORE
	const BEFORE = "BEFORE";

	//------------------------------------------------------------------------------------------ READ
	const READ = "READ";

	//----------------------------------------------------------------------------------------- WRITE
	const WRITE = "WRITE";

	//----------------------------------------------------------------------------------- $joinpoints
	/**
	 * Keys are : class name, method name
	 * values are : array(kind, advice)
	 * @var array
	 */
	public static $joinpoints = array();

	//------------------------------------------------------------------------------------------- add
	public static function add($when, $joinpoint, $advice)
	{
		// string for old calls compatibility (todo : migrate calls and remove this if)
		if (is_string($joinpoint)) {
			// method
			if (substr($joinpoint, -2) === "()") {
				$joinpoint = explode("->", substr($joinpoint, 0, -2));
			}
			// property
			else {
				if (strpos($joinpoint, " ")) {
					list($when, $joinpoint) = explode(" ", $joinpoint);
				}
				$joinpoint = explode("->", $joinpoint);
			}
		}
		// array
		switch ($when) {
			case Aop::AFTER:  self::addAfterMethodCall ($joinpoint, $advice); break;
			case Aop::AROUND: self::addAroundMethodCall($joinpoint, $advice); break;
			case Aop::BEFORE: self::addBeforeMethodCall($joinpoint, $advice); break;
			case Aop::READ:   self::addOnPropertyRead  ($joinpoint, $advice); break;
			case Aop::WRITE:  self::addOnPropertyWrite ($joinpoint, $advice); break;
		}
	}

	//----------------------------------------------------------------------------- addOnPropertyRead
	public static function addOnPropertyRead($joinpoint, $advice)
	{
	}

	//---------------------------------------------------------------------------- addOnPropertyWrite
	public static function addOnPropertyWrite($joinpoint, $advice)
	{
	}

	//---------------------------------------------------------------------------- addAfterMethodCall
	public static function addAfterMethodCall($joinpoint, $advice)
	{
	}

	//--------------------------------------------------------------------------- addAroundMethodCall
	public static function addAroundMethodCall($joinpoint, $advice)
	{
	}

	//--------------------------------------------------------------------------- addBeforeMethodCall
	public static function addBeforeMethodCall($joinpoint, $advice)
	{
		$count = isset(self::$joinpoints[$joinpoint[0]][$joinpoint[1]])
			? count(self::$joinpoints[$joinpoint[0]][$joinpoint[1]]) : 0;
		$method = new Reflection_Method($joinpoint[0], $joinpoint[1]);

		$arguments = join(", ", $method->getArguments());
		$advice_string = "array('" . join("', '", $advice) . "')";
		$advice_arguments = '$this' . ($arguments ? (', &$' . join(', &$', array_keys($method->getArguments()))) : '');
		$process_arguments = $arguments ? ('$' . join(', $', array_keys($method->getArguments()))) : '';

		$advice_call = "call_user_func_array($advice_string, array($advice_arguments));";
		$process_call = "return \$this->_$joinpoint[1]_$count($process_arguments);";
		$code = $advice_call . "\n" . $process_call;

		runkit_method_rename($joinpoint[0], $joinpoint[1], "_" . $joinpoint[1] . "_" . $count);
		runkit_method_add($joinpoint[0], $joinpoint[1], $arguments, $code);
	}

}

class Test
{

	/**
	 * @param $arg1 string
	 * @param $arg string
	 * @return string
	 */
	public function method($arg1, $arg2 = "test")
	{
		return $arg1 . " " . $arg2;
	}

}

class Advice
{

	/**
	 * @param $arg string
	 */
	public static function advised($object, &$arg1, &$arg2)
	{
		echo "call advice of " . print_r($object, true) . "<br>";
		$arg2 .= " advised !";
	}

}

Aop::addBeforeMethodCall(
	array('SAF\Framework\Test', 'method'),
	array('SAF\Framework\Advice', 'advised')
);

$test = new Test();
echo $test->method("un");
