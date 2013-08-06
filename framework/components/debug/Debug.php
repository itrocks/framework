<?php
namespace SAF\Framework;

use \ReflectionClass;

/**
 * Debug functions that are missing in standard
 */
abstract class Debug
{

	//--------------------------------------------------------------------------------------- display
	/**
	 * @param $text string
	 */
	public static function display($text)
	{
		echo date("Y-m-d H:i:s") . " " . $text . "<br>\n";
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
	 * @param $return boolean true if you want to return the result instead of displaying it
	 * @param $pre    boolean display result between <pre> and </pre>
	 * @return boolean|array true if $return was false, else returns the result array
	 */
	public static function globalDump($return = false, $pre = true)
	{
		$dump['$GLOBALS'] = $GLOBALS;
		$dump['$_SERVER'] = $_SERVER;
		foreach (get_declared_classes() as $class) {
			foreach ((new ReflectionClass($class))->getProperties() as $property) {
				if ($property->isStatic()) {
					if (!$property->isPublic()) {
						$property->setAccessible(true);
						$not_accessible = true;
					}
					else {
						$not_accessible = false;
					}
					$dump['STATIC'][$class][$property->name] = $property->getValue();
					if ($not_accessible) {
						$property->setAccessible(false);
					}
				}
			}
		}
		if ($return) {
			return $dump;
		}
		echo ($pre ? "<pre>" : "") . print_r($dump, true) . ($pre ? "</pre>" : "");
		return true;
	}

	//-------------------------------------------------------------------------------------- whatGrew
	/**
	 * This tells what variable from the global dump growed since the last call to whatGrowed.
	 * Usefull for memory leaks detection, but doubles the quantity of memory used
	 * The first call does nothing but initialize the global dump history
	 *
	 * @param $old  mixed the old value
	 * @param $new  mixed the new value
	 * @param $path string the value path
	 * @return array returns an associative array of path and sizes (old, new)
	 */
	public static function whatGrew($old = null, $new = null, $path = "")
	{
		$result = array();
		if (!isset($old) && !isset($new)) {
			static $old_dump;
			$new_dump = self::globalDump(true);
			if (isset($old_dump)) {
				$result = self::whatGrew($old_dump, $new_dump);
			}
			$old_dump = $new_dump;
		}
		else {
			foreach ($old as $key => $value) if (isset($new[$key])) {
				$old_size = strlen(serialize($value));
				$new_size = strlen(serialize($new[$key]));
				if ($old_size < $new_size) {
					$sub_path = $path ? ($path . "." . $key) : $key;
					$result = array_merge(
						array($sub_path => "from $old_size to $new_size"),
						self::whatGrew($value, $new[$key], $sub_path)
					);
				}
			}
		}
		return $result;
	}

}
