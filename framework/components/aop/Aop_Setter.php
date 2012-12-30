<?php
namespace SAF\Framework;
use AopJoinpoint;

require_once "framework/core/toolbox/Aop.php";
require_once "framework/core/reflection/Reflection_Property.php";

abstract class Aop_Setter extends Aop implements Plugin
{

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::add("after",
			__NAMESPACE__ . "\\Autoloader->classLoadEvent()",
			array(__CLASS__, "registerSettersAop")
		);
	}

	//------------------------------------------------------------------------------- registerSetters
	/**
	 * Auto-register properties setters for a given class name
	 *
	 * Call this each time a class is declared (ie at end of Autoloader->autoload()) to automatically register AOP special setters for object properties.
	 * This uses the property @setter annotation to know what setter to use.
	 * Specific Aop::getMethod() setters are allowed shortcuts for SAF\Framework\Aop_Setter::getMethod().
	 *
	 * @todo check phpdoc
	 * @param string $class_name
	 */
	public static function registerSetters($class_name)
	{
		parent::registerProperties($class_name, "setter", "write");
	}

	//---------------------------------------------------------------------------- registerSettersAop
	/**
	 * AOP auto-registerer call (to register after Autoloader->autoload(), crashes with AOP-PHP 0.2.0)
	 *
	 * @param AopJoinpoint $joinpoint
	 */
	public static function registerSettersAop(AopJoinpoint $joinpoint)
	{
		if ($joinpoint->getReturnedValue()) {
			list($class_name) = $joinpoint->getArguments();
			parent::registerProperties(Namespaces::fullClassName($class_name), "setter", "write");
		}
	}

	//---------------------------------------------------------------------------------------- setter
	public static function setter()
	{
		echo "setter";
	}

}
