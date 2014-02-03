<?php
namespace SAF\Framework;

/**
 * Aop calls getters
 */
class Aop_Getter extends Aop implements Plugin
{

	//--------------------------------------------------------------------------------------- $ignore
	/**
	 * @var boolean
	 */
	public static $ignore = false;

	//---------------------------------------------------------------------------------------- getAll
	/**
	 * Register this using "@getter Aop::getAll" for a property that points on all existing elements
	 * of a collection
	 *
	 * @param $value     object[]
	 * @param $joinpoint Property_Read_Joinpoint
	 * @return object[]
	 */
	public function getAll(&$value, Property_Read_Joinpoint $joinpoint)
	{
		if (!self::$ignore && !isset($value)) {
			$type_name = $joinpoint->getProperty()->getType()->getElementTypeAsString();
			$value = Getter::getAll(null, $type_name);
		}
		return $value;
	}

	//--------------------------------------------------------------------------------- getCollection
	/**
	 * Register this for any object collection property using "@link Collection" annotation
	 *
	 * @param $value     Component[]
	 * @param $joinpoint Property_Read_Joinpoint
	 * @return Component[]
	 */
	public function getCollection(&$value, Property_Read_Joinpoint $joinpoint)
	{
		if (!self::$ignore && !isset($value)) {
			$type_name = $joinpoint->getProperty()->getType()->getElementTypeAsString();
			$value = Getter::getCollection(
				null, $type_name, $joinpoint->object, $joinpoint->property_name
			);
		}
		return $value;
	}

	//----------------------------------------------------------------------------------- getDateTime
	/**
	 * Register this for any Date_Time property using "@link DateTime" annotation
	 *
	 * @param $value Date_Time|string
	 * @return Date_Time
	 */
	public function getDateTime(&$value)
	{
		if (!self::$ignore && is_string($value)) {
			$value = Date_Time::fromISO($value);
		}
		return $value;
	}

	//--------------------------------------------------------------------------------------- getFile
	/**
	 * Register this for any object property using "@link File" annotation
	 *
	 * @param $value     object
	 * @param $joinpoint Property_Read_Joinpoint
	 * @return object
	 */
	public function getFile(&$value, Property_Read_Joinpoint $joinpoint)
	{
		return $this->getObject($value, $joinpoint);
	}

	//---------------------------------------------------------------------------------------- getMap
	/**
	 * Register this for any object map property using "@link Map" annotation
	 *
	 * @param $value     object[]
	 * @param $joinpoint Property_Read_Joinpoint
	 * @return object[]
	 */
	public function getMap(&$value, Property_Read_Joinpoint $joinpoint)
	{
		if (!self::$ignore && !isset($value)) {
			$property = $joinpoint->getProperty()->getType()->getElementTypeAsString();
			$value = Getter::getMap(null, $joinpoint->object, $property);
		}
		return $value;
	}

	//------------------------------------------------------------------------------------- getObject
	/**
	 * @param $value     object
	 * @param $joinpoint Property_Read_Joinpoint
	 * @return object
	 */
	public function getObject(&$value, Property_Read_Joinpoint $joinpoint)
	{
		if (!self::$ignore && !isset($value)) {
			$property = $joinpoint->getProperty();
			$type = $property->getType()->asString();
			$value = Getter::getObject(null, $type, $joinpoint->object, $property);
			if (!is_object($value)) {
				$value = Null_Object::create($type);
			}
		}
		return $value;
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Plugin_Register
	 */
	public function register(Plugin_Register $register)
	{
		$dealer = $register->dealer;
		$dealer->afterMethodCall(
			array('SAF\Framework\Autoloader', "includeClass"),
			array($this, "registerIncludedGettersAop")
		);
		$dealer->afterMethodCall(
			array('SAF\Framework\Class_Builder', "buildClassSource"),
			array($this, "registerBuiltGettersAop")
		);
	}

	//----------------------------------------------------------------------- registerBuiltGettersAop
	/**
	 * AOP auto-registerer call
	 *
	 * @param $class_name string
	 */
	public function registerBuiltGettersAop($class_name)
	{
		parent::registerProperties($class_name, "getter", "read");
	}

	//------------------------------------------------------------------------------- registerGetters
	/**
	 * Auto-register properties getters for a given class name
	 *
	 * Call this each time a class is declared (ie at end of Autoloader->autoload()) to automatically register AOP special getters for object properties.
	 * This uses the property @getter annotation to know what getter to use.
	 * Specific Aop::getMethod() getters are allowed shortcuts for SAF\Framework\Aop_Getter::getMethod().
	 *
	 * @param $class_name string
	 */
	public function registerGetters($class_name)
	{
		parent::registerProperties($class_name, "getter", "read");
	}

	//-------------------------------------------------------------------- registerIncludedGettersAop
	/**
	 * AOP auto-register call
	 *
	 * @param $class_name string
	 * @param $result     string
	 */
	public function registerIncludedGettersAop($class_name, $result)
	{
		if ($result) {
			$class_name = Autoloader::rectifyClassName($class_name, $result);
			parent::registerProperties($class_name, "getter", "read");
		}
	}

}
