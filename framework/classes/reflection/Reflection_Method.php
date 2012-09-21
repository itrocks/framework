<?php
namespace SAF\Framework;
use ReflectionClass;
use ReflectionMethod;

require_once "framework/classes/reflection/annotations/Annotation.php";
require_once "framework/classes/reflection/annotations/Annotation_Parser.php";
require_once "framework/classes/reflection/annotations/Annoted.php";
require_once "framework/classes/reflection/Has_Doc_Comment.php";
require_once "framework/classes/reflection/Reflection_Class.php";

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
	 * @var multitype:multitype:Reflection_Class
	 */
	private static $cache = array();

	//--------------------------------------------------------------------------------- getInstanceOf
	/**
	 * Return Reflection_Method instance for a class name, object, ReflectionClass, Reflection_Class, ReflectionMethod object
	 *
	 * @param string | object | ReflectionClass | ReflectionMethod $of_class
	 * @param string $of_name do not set this if $of_class is a ReflectionMethod
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
		if (isset(Reflection_Method::$cache[$of_class][$of_name])) {
			$method = Reflection_Method::$cache[$of_class][$of_name];
		}
		else {
			$method = new Reflection_Method($of_class, $of_name);
			Reflection_Method::$cache[$of_class][$of_name] = $method;
		}
		return $method;
	}

}
