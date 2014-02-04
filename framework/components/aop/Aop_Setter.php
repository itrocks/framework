<?php
namespace SAF\Framework;

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
		$aop = $register->aop;
		$aop->afterMethod(
			array('SAF\Framework\Autoloader', "includeClass"),
			array($this, "registerIncludedSettersAop")
		);
		$aop->afterMethod(
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
	 * Specific Aop::getMethod() setters are allowed shortcuts for SAF\AOP_Setter::getMethod().
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

	//------------------------------------------------------------------------------------- setObject
	/**
	 * When setting an object, set its object identifier too
	 *
	 * @param $value     object
	 * @param $joinpoint Property_Write_Joinpoint
	 */
	public function setObject($value, Property_Write_Joinpoint $joinpoint)
	{
		$id_property = "id_" . $joinpoint->property_name;
		$identifier = Dao::getObjectIdentifier($value);
		if (is_object($value) && !empty($identifier)) {
			$joinpoint->object->$id_property = $identifier;
		}
		else {
			unset($joinpoint->object->$id_property);
		}
	}

}
