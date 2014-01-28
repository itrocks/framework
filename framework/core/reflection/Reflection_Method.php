<?php
namespace SAF\Framework;

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

	//---------------------------------------------------------------------------------- getParameter
	/**
	 * @param $parameter_name string
	 * @return Reflection_Parameter
	 */
	public function getParameter($parameter_name)
	{
		return $this->getParameters()[$parameter_name];
	}

	//--------------------------------------------------------------------------------- getParameters
	/**
	 * @param $by_name boolean
	 * @return Reflection_Parameter[]
	 */
	public function getParameters($by_name = true)
	{
		$parameters = array();
		foreach (parent::getParameters() as $key => $parameter) {
			$parameters[$by_name ? $parameter->name : $key] = new Reflection_Parameter(
				array($this->class, $this->name), $parameter->name
			);
		}
		return $parameters;
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
