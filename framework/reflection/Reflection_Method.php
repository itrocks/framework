<?php
namespace SAF\Framework;
use Exception;
use ReflectionMethod;

class Reflection_Method extends ReflectionMethod implements Annoted
{

	/**
	 * @var integer
	 */
	const ALL = 1799;

	private static $cache = array();

	//--------------------------------------------------------------------------------- getInstanceOf
	/**
	 * @param ReflectionProperty | string $of_class
	 * @param string                      $of_name only if $of_class is a string too
	 */
	public static function getInstanceOf($of_class, $of_name = null)
	{
		if (is_string($of_class) && !is_string($of_name)) {
			throw new Exception(
				"Reflection_Method::getInstanceOf($of_class, $of_name) illegal parameter 2"
			);
		}
		if ($of_class instanceof ReflectionMethod) {
			$of_class = $of_class->class;
			$of_name  = $of_class->name;
		}
		$method = Reflection_Method::$cache[$of_class][$of_name];
		if (!$method) {
			$method = new Reflection_Method($of_class, $of_name);
			Reflection_Method::$cache[$of_class][$of_name] = $method;
		}
		return $method;
	}

	//--------------------------------------------------------------------------------- getAnnotation
	public function getAnnotation($annotation_name)
	{
		return Annotation_Parser::byName($this->getDocComment(), $annotation_name);
	}

	//--------------------------------------------------------------------------------- getReturnType
	public function getReturnType()
	{
		return $this->getAnnotation("return")->type;
	}

}
