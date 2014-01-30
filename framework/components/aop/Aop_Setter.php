<?php
namespace SAF\Framework;

use AopJoinpoint;

/**
 * Aop call setters
 */
class Aop_Setter extends Aop implements Plugin
{

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Plugin_Register
	 */
	public function register(Plugin_Register $register)
	{
		$dealer = $register->dealer;
		$dealer->afterMethodCall(
			array('SAF\Framework\Autoloader', "includeClass"),
			array($this, "registerIncludedSettersAop")
		);
		$dealer->afterMethodCall(
			array('SAF\Framework\Class_Builder', "buildClassSource"),
			array($this, "registerBuiltSettersAop")
		);
	}

	//----------------------------------------------------------------------- registerBuiltSettersAop
	/**
	 * AOP auto-registerer call
	 *
	 * @param $class_name string
	 */
	public function registerBuiltSettersAop($class_name)
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
	public function registerSetters($class_name)
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
	public function registerIncludedSettersAop($class_name, $result)
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
	public function setObject(AopJoinpoint $joinpoint)
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
