<?php
namespace SAF\Framework;
use AopJoinpoint;
use ReflectionClass;

/** @noinspection PhpIncludeInspection */
require_once "framework/core/toolbox/Current_With_Default.php";
/** @noinspection PhpIncludeInspection */
require_once "framework/core/toolbox/Plugin.php";

class Builder implements Plugin
{
	use Current_With_Default { current as private dCurrent; }

	//--------------------------------------------------------------------------------- $replacements
	/**
	 * @var string[] key is parent class name associated to replacement class
	 */
	private $replacements;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $replacements string[] key is parent class name associated to replacement class
	 */
	public function __construct($replacements = array())
	{
		$this->replacements = $replacements;
	}

	//------------------------------------------------------------------ afterNamespacesFullClassName
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function afterNamespacesFullClassName(AopJoinpoint $joinpoint)
	{
		if (Namespaces::isShortClassName($joinpoint->getArguments()[0])) {
			self::onMethodWithReturnedValue($joinpoint);
		}
	}

	//------------------------------------------------------------------------------------- className
	/**
	 * @param $class_name string
	 * @return string
	 */
	public static function className($class_name)
	{
		return self::current()->replacementClassName($class_name);
	}

	//---------------------------------------------------------------------------------------- create
	/**
	 * @param $class_name string
	 * @param $args       array|null
	 * @return object
	 */
	public static function create($class_name, $args = null)
	{
		return isset($args)
			? self::current()->newInstanceArgs($class_name, $args)
			: self::current()->newInstance($class_name);
	}

	//--------------------------------------------------------------------------------------- current
	/**
	 * @param $set_current Builder
	 * @return Builder
	 */
	public static function current(Builder $set_current = null)
	{
		return self::dCurrent($set_current);
	}

	//----------------------------------------------------------------------------------- isObjectSet
	/**
	 * Returns true if any property of $object is set and different than its default value
	 *
	 * @param $object
	 * @return boolean
	 */
	public static function isObjectSet($object)
	{
		$result = false;
		$class = Reflection_Class::getInstanceOf($object);
		$defaults = $class->getDefaultProperties();
		foreach ($class->accessProperties() as $property) if (!$property->isStatic()) {
			$value = $property->getValue($object);
			if (isset($value)) {
				$default = isset($defaults[$property->name])
					? $defaults[$property->name]
					: ($property->getType()->getDefaultValue());
				if (is_object($value) && !self::isObjectSet($value)) {
					$value = null;
				}
				if ($value != $default) {
					$result = true;
					break;
				}
			}
		}
		$class->accessPropertiesDone();
		return $result;
	}

	//----------------------------------------------------------------------------------- newInstance
	/**
	 * Return a new instance of given $class_name, using replacement class if exist
	 *
	 * @param $class_name string may be short or full class name
	 * @return object
	 */
	public function newInstance($class_name)
	{
		$class_name = $this->replacementClassName($class_name);
		return new $class_name();
	}

	//------------------------------------------------------------------------------- newInstanceArgs
	/**
	 * Return a new instance of given $class_name, using replacement class if exist
	 *
	 * @param $class_name string may be short or full class name
	 * @param $args       array
	 * @return object
	 */
	public function newInstanceArgs($class_name, $args)
	{
		return (new ReflectionClass($this->replacementClassName($class_name)))->newInstanceArgs($args);
	}

	//---------------------------------------------------------------------- onClassNamePropertyWrite
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function onClassNamePropertyWrite(AopJoinpoint $joinpoint)
	{
		static $antiloop = false;
		if (!$antiloop) {
			$assigned_value = $joinpoint->getAssignedValue();
			$class_name = Builder::current()->replacementClassName($assigned_value);
			if ($class_name !== $assigned_value) {
				$property_name = $joinpoint->getPropertyName();
				$antiloop = true;
				$joinpoint->getObject()->$property_name = $class_name;
				$antiloop = false;
			}
		}
	}

	//------------------------------------------------------------------ onFullClassNamePropertyWrite
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function onFullClassNamePropertyWrite(AopJoinpoint $joinpoint)
	{
		Builder::onClassNamePropertyWrite($joinpoint, true);
	}

	//------------------------------------------------------------------------- onMethodWithClassName
	/**
	 * @param $joinpoint AopJoinpoint
	 * @param $index integer
	 */
	private static function onMethodWithClassName(AopJoinpoint $joinpoint, $index)
	{
		$arguments = $joinpoint->getArguments();
		$class_name = Builder::current()->replacementClassName($arguments[$index]);
		if ($class_name !== $arguments[$index]) {
			$arguments[$index] = $class_name;
			$joinpoint->setArguments($arguments);
		}
	}

	//------------------------------------------------------------------------ onMethodWithClassName0
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function onMethodWithClassName0(AopJoinpoint $joinpoint)
	{
		Builder::onMethodWithClassName($joinpoint, 0);
	}

	//------------------------------------------------------------------------ onMethodWithClassName1
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function onMethodWithClassName1(AopJoinpoint $joinpoint)
	{
		Builder::onMethodWithClassName($joinpoint, 1);
	}

	//----------------------------------------------------------------------- onMethodWithReturnValue
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function onMethodWithReturnedValue(AopJoinpoint $joinpoint)
	{
		$joinpoint->setReturnedValue(
			Builder::current()->replacementClassName($joinpoint->getReturnedValue())
		);
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::add(Aop::AFTER,
			'SAF\Framework\Set->elementClassNameOf()',
			array(__CLASS__, "onMethodWithReturnedValue")
		);
		Aop::add(Aop::AFTER,
			'SAF\Framework\Namespaces->fullClassName()',
			array(__CLASS__, "afterNamespacesFullClassName")
		);
		Aop::add(Aop::BEFORE,
			'SAF\Framework\Getter->getCollection()',
			array(__CLASS__, "onMethodWithClassName1")
		);
		Aop::add(Aop::BEFORE,
			'SAF\Framework\Getter->getObject()',
			array(__CLASS__, "onMethodWithClassName1")
		);
		Aop::add(Aop::BEFORE,
			'SAF\Framework\Search_Object->newInstance()',
			array(__CLASS__, "onMethodWithClassName0")
		);
	}

	//-------------------------------------------------------------------------- replacementClassName
	/**
	 * Gets replacement class name for a parent class name or a list of traits to implement
	 *
	 * @param $class_name string can be short or full class name
	 * @return string
	 */
	private function replacementClassName($class_name)
	{
		$result = isset($this->replacements[$class_name])
			? $this->replacements[$class_name]
			: $class_name;
		return is_array($result)
			? Class_Builder::build($class_name, $result)
			: $result;
	}

	//-------------------------------------------------------------------------------- setReplacement
	/**
	 * Sets a new replacement
	 *
	 * Returns the ole replacement class name as you can set it back at will
	 *
	 * @param $class_name             string
	 * @param $replacement_class_name string|null
	 * @return string|null old replacement class name
	 */
	public function setReplacement($class_name, $replacement_class_name)
	{
		$result = isset($this->replacements[$class_name]) ? $this->replacements[$class_name] : null;
		if (!isset($replacement_class_name)) {
			unset($this->replacements[$class_name]);
		}
		else {
			$this->replacements[$class_name] = $replacement_class_name;
		}
		return $result;
	}

}
