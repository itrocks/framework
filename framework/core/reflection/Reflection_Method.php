<?php
namespace SAF\Framework;
use ReflectionClass;
use ReflectionMethod;

require_once "framework/core/reflection/annotations/Annotation.php";
require_once "framework/core/reflection/annotations/Annotation_Parser.php";
require_once "framework/core/reflection/annotations/Annoted.php";
require_once "framework/core/reflection/Has_Doc_Comment.php";
require_once "framework/core/reflection/Reflection_Class.php";

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

	//---------------------------------------------------------------------------------------- $cache
	/**
	 * Cache Reflection_Method objects for each class and method name
	 *
	 * @var Reflection_Class[]
	 */
	private static $cache = array();

	//--------------------------------------------------------------------------------- getInstanceOf
	/**
	 * Return Reflection_Method instance for a class name, object, ReflectionClass, Reflection_Class, ReflectionMethod object
	 *
	 * @param $of_class string | object | ReflectionClass | ReflectionMethod
	 * @param $of_name string $of_name do not set this if is a ReflectionMethod
	 * @return Reflection_Method
	 */
	public static function getInstanceOf($of_class, $of_name = null)
	{
		if ($of_class instanceof ReflectionMethod) {
			$of_name  = $of_class->name;
			$of_class = $of_class->class;
		}
		elseif ($of_class instanceof ReflectionClass) {
			$of_class = $of_class->name;
		}
		elseif (is_object($of_class)) {
			$of_class = get_class($of_class);
		}
		if (isset(self::$cache[$of_class][$of_name])) {
			$method = self::$cache[$of_class][$of_name];
		}
		else {
			$method = new Reflection_Method($of_class, $of_name);
			self::$cache[$of_class][$of_name] = $method;
		}
		return $method;
	}

}
