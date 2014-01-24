<?php
namespace SAF\Framework;

use ReflectionClass;
use ReflectionMethod;

/** @noinspection PhpIncludeInspection */
require_once "framework/core/reflection/annotations/Annotation.php";
/** @noinspection PhpIncludeInspection */
require_once "framework/core/reflection/annotations/Annotation_Parser.php";
/** @noinspection PhpIncludeInspection */
require_once "framework/core/reflection/annotations/Annoted.php";
/** @noinspection PhpIncludeInspection */
require_once "framework/core/reflection/Has_Doc_Comment.php";
/** @noinspection PhpIncludeInspection */
require_once "framework/core/reflection/Reflection_Class.php";

/**
 * A rich extension of the PHP ReflectionMethod class, adding :
 * - annotations management
 */
class Reflection_Method extends ReflectionMethod implements Has_Doc_Comment
{
	use Annoted;

	//------------------------------------------------------------------------------------------- ALL
	/**
	 * Another constant for default Reflection_Class::getMethods() filter
	 *
	 * @var integer
	 */
	const ALL = 1799;

	//------------------------------------------------------------------------------ $arguments_cache
	/**
	 * @var array
	 */
	private static $arguments_cache;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name  string
	 * @param $method_name string
	 */
	public function __construct($class_name, $method_name)
	{
		if (!(is_string($class_name) && is_string($method_name))) {
			trigger_error(__CLASS__ . " constructor needs strings", E_USER_ERROR);
		}
		parent::__construct($class_name, $method_name);
	}

	//------------------------------------------------------------------------ getAnnotationCachePath
	/**
	 * @return string[]
	 */
	protected function getAnnotationCachePath()
	{
		return array($this->class, $this->name . "()");
	}

	//----------------------------------------------------------------------------------- getArgument
	/**
	 * @param $argument_name string
	 * @return Reflection_Argument
	 */
	public function getArgument($argument_name)
	{
		return $this->getArguments()[$argument_name];
	}

	//---------------------------------------------------------------------------------- getArguments
	/**
	 * @return Reflection_Argument[]
	 */
	public function getArguments()
	{
		if (!isset(self::$arguments_cache[$this->class][$this->name])) {
			$arguments = array();
			preg_match_all("/Parameter .* \[ (.*?) \]\n/", strval($this), $matches);
			foreach ($matches[1] as $match) {
				// required
				list($required, $argument) = explode(" ", $match, 2);
				$required = ($required == "<required>");
				// default
				if ($required) {
					$default = null;
				}
				else {
					list($argument, $default) = explode(" = ", $argument, 2);
					if ((substr($default, 0, 1) === "'") && (substr($default, -1) === "'")) {
						$default = substr($default, 1, -1);
					}
				}
				// argument
				$argument = substr($argument, 1);
				// final argument
				$arguments[$argument] = new Reflection_Argument(
					$this->class, $this->name, $argument, $default, $required
				);
			}
			self::$arguments_cache[$this->class][$this->name] = $arguments;
			return $arguments;
		}
		else {
			return self::$arguments_cache[$this->class][$this->name];
		}
	}

	//--------------------------------------------------------------------------------- getDocComment
	/**
	 * @param $parent boolean
	 * @return string
	 */
	public function getDocComment($parent = false)
	{
		// TODO parent methods read
		return parent::getDocComment();
	}

}
