<?php
namespace SAF\Framework;

use AopJoinpoint;

/** @noinspection PhpIncludeInspection */
require_once "framework/core/aop/Aop.php";
/** @noinspection PhpIncludeInspection */
require_once "framework/core/reflection/Reflection_Property.php";

/**
 * Aop call setters
 */
abstract class Aop_Setter extends Aop implements Plugin
{

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::addAfterMethodCall(
			array('SAF\Framework\Autoloader', "includeClass"),
			array(__CLASS__, "registerIncludedSettersAop")
		);
		Aop::addAfterMethodCall(
			array('SAF\Framework\Class_Builder', "buildClassSource"),
			array(__CLASS__, "registerBuiltSettersAop")
		);
	}

	//----------------------------------------------------------------------- registerBuiltSettersAop
	/**
	 * AOP auto-registerer call
	 *
	 * @param $class_name string
	 */
	public static function registerBuiltSettersAop($class_name)
	{
		parent::registerProperties($class_name, "setter", "write");
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
		parent::registerProperties($class_name, "setter", "write");
	}

	//-------------------------------------------------------------------- registerIncludedSettersAop
	/**
	 * AOP auto-registerer call
	 *
	 * @param $class_name string
	 * @param $result     string
	 */
	public static function registerIncludedSettersAop($class_name, $result)
	{
		if ($result) {
			$class_name = Autoloader::rectifyClassName($class_name, $result);
			parent::registerProperties($class_name, "setter", "write");
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
