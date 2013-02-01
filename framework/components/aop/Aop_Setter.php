<?php
namespace SAF\Framework;
use AopJoinpoint;

/** @noinspection PhpIncludeInspection called from index.php */
require_once "framework/core/toolbox/Aop.php";
/** @noinspection PhpIncludeInspection called from index.php */
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
	 * @param $class_name string
	 */
	public static function registerSetters($class_name)
	{
		parent::registerProperties($class_name, "setter", "after", "write");
	}

	//---------------------------------------------------------------------------- registerSettersAop
	/**
	 * AOP auto-registerer call (to register after Autoloader->autoload(), crashes with AOP-PHP 0.2.0)
	 *
	 * @param $joinpoint AopJoinpoint
	 */
	public static function registerSettersAop(AopJoinpoint $joinpoint)
	{
		if ($joinpoint->getReturnedValue()) {
			list($class_name) = $joinpoint->getArguments();
			parent::registerProperties(Namespaces::fullClassName($class_name), "setter", "after", "write");
		}
	}

	//------------------------------------------------------------------------------------- setParent
	/**
	 * When setting an object, set its object identifier too
	 *
	 * @todo unused : please test it
	 * @param $joinpoint AopJoinpoint
	 */
	public static function setObject(AopJoinpoint $joinpoint)
	{
		$object = $joinpoint->getObject();
		$id_property = "id_" . $joinpoint->getPropertyName();
		$value = $joinpoint->getAssignedValue();
		$identifier = Dao::getObjectIdentifier($value);
		if (is_object($value) && !empty($identifier)) {
			$object->$id_property = $identifier;
		}
		else {
			unset($object->$id_property);
		}
	}

}
