<?php
namespace SAF\Framework;
use AopJoinpoint;
use ReflectionClass;

class Object_Builder
{
	use Current { Current as private pCurrent; }

	//---------------------------------------------------------------------------------- $new_classes
	/**
	 * @var multitype:string key is parent class name associated to replacement class
	 */
	private $new_classes;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param multitype:string $new_classes key is parent class name associated to replacement class
	 */
	public function __construct($new_classes)
	{
		$this->new_classes = $new_classes;
	}

	//--------------------------------------------------------------------------------------- current
	/**
	 * @param Object_Builder $set_current
	 * @return Object_Builder
	 */
	public static function current(Object_Builder $set_current = null)
	{
		return self::pCurrent($set_current);
	}

	//----------------------------------------------------------------------------------- newInstance
	/**
	 * Return a new instance of given $class_name, using replacement class if exist
	 *
	 * @param string $class_name may be short or full class name
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
	 * @param string $class_name may be short or full class name
	 * @return object
	 */
	public function newInstanceArgs($class_name, $args)
	{
		return (new ReflectionClass($this->replacementClassName($class_name)))->newInstanceArgs($args);
	}

	//---------------------------------------------------------------------- onClassNamePropertyWrite
	/**
	 * @param AopJoinpoint $joinpoint
	 * @param boolean $full
	 */
	private static function onClassNamePropertyWrite(AopJoinpoint $joinpoint, $full)
	{
		static $antiloop = false;
		if (!$antiloop) {
			$assigned_value = $joinpoint->getAssignedValue();
			$class_name = Object_Builder::current()->replacementClassName($assigned_value);
			if ($class_name !== $assigned_value) {
				$property_name = $joinpoint->getPropertyName();
				$antiloop = true;
				$joinpoint->getObject()->$property_name = $full
					? $class_name
					: Namespaces::shortClassName($class_name);
				$antiloop = false;
			}
		}
	}

	//------------------------------------------------------------------ onFullClassNamePropertyWrite
	/**
	 * @param AopJoinpoint $joinpoint
	 */
	public static function onFullClassNamePropertyWrite(AopJoinpoint $joinpoint)
	{
		Object_Builder::onClassNamePropertyWrite($joinpoint, true);
	}

	//------------------------------------------------------------------------- onMethodWithClassName
	/**
	 * @param AopJoinpoint $joinpoint
	 * @param integer $index
	 */
	private static function onMethodWithClassName(AopJoinpoint $joinpoint, $index)
	{
		$arguments = $joinpoint->getArguments();
		$class_name = Object_Builder::current()->replacementClassName($arguments[$index]);
		if ($class_name !== $arguments[$index]) {
			$arguments[$index] = $class_name;
			$joinpoint->setArguments($arguments);
		}
	}

	//------------------------------------------------------------------------ onMethodWithClassName0
	/**
	 * @param AopJoinpoint $joinpoint
	 */
	public static function onMethodWithClassName0(AopJoinpoint $joinpoint)
	{
		Object_Builder::onMethodWithClassName($joinpoint, 0);
	}

	//------------------------------------------------------------------------ onMethodWithClassName1
	/**
	 * @param AopJoinpoint $joinpoint
	 */
	public static function onMethodWithClassName1(AopJoinpoint $joinpoint)
	{
		Object_Builder::onMethodWithClassName($joinpoint, 1);
	}

	//----------------------------------------------------------------- onShortClassNamePropertyWrite
	/**
	 * @param AopJoinpoint $joinpoint
	 */
	public static function onShortClassNamePropertyWrite(AopJoinpoint $joinpoint)
	{
		Object_Builder::onClassNamePropertyWrite($joinpoint, false);
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		aop_add_after(
			"write " . __NAMESPACE__ . "\\Controller_Uri->controller_name",
			array(__NAMESPACE__ . "\\Object_Builder", "onShortClassNamePropertyWrite")
		);
		aop_add_before(
			__NAMESPACE__ . "\\Getter->getCollection()",
			array(__NAMESPACE__ . "\\Object_Builder", "onMethodWithClassName1")
		);
		aop_add_before(
			__NAMESPACE__ . "\\Getter->getObject()",
			array(__NAMESPACE__ . "\\Object_Builder", "onMethodWithClassName1")
		);
		aop_add_before(
			__NAMESPACE__ . "\\Search_Object->newInstance()",
			array(__NAMESPACE__ . "\\Object_Builder", "onMethodWithClassName0")
		);
	}

	//-------------------------------------------------------------------------- replacementClassName
	/**
	 * Gets replacement class name for a parent class name
	 *
	 * @param string $class_name can be short or full class name
	 * @return string
	 */
	public function replacementClassName($class_name)
	{
		$class_name = Namespaces::fullClassName($class_name);
		return isset($this->new_classes[$class_name])
			? $this->new_classes[$class_name]
			: $class_name;
	}

}
