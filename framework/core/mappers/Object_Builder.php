<?php
namespace SAF\Framework;
use AopJoinpoint;
use ReflectionClass;

class Object_Builder implements Plugin
{
	use Current_With_Default { current as private dCurrent; }

	//---------------------------------------------------------------------------------- $new_classes
	/**
	 * @var string[] key is parent class name associated to replacement class
	 */
	private $new_classes;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $new_classes string[] key is parent class name associated to replacement class
	 */
	public function __construct($new_classes = array())
	{
		$this->new_classes = $new_classes;
	}

	//--------------------------------------------------------------------------------------- current
	/**
	 * @param $set_current Object_Builder
	 * @return Object_Builder
	 */
	public static function current(Object_Builder $set_current = null)
	{
		return self::dCurrent($set_current);
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
	 * @param $full boolean
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
	 * @param $joinpoint AopJoinpoint
	 */
	public static function onFullClassNamePropertyWrite(AopJoinpoint $joinpoint)
	{
		Object_Builder::onClassNamePropertyWrite($joinpoint, true);
	}

	//------------------------------------------------------------------------- onMethodWithClassName
	/**
	 * @param $joinpoint AopJoinpoint
	 * @param $index integer
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
	 * @param $joinpoint AopJoinpoint
	 */
	public static function onMethodWithClassName0(AopJoinpoint $joinpoint)
	{
		Object_Builder::onMethodWithClassName($joinpoint, 0);
	}

	//------------------------------------------------------------------------ onMethodWithClassName1
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function onMethodWithClassName1(AopJoinpoint $joinpoint)
	{
		Object_Builder::onMethodWithClassName($joinpoint, 1);
	}

	//----------------------------------------------------------------------- onMethodWithReturnValue
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function onMethodWithReturnedValue(AopJoinpoint $joinpoint)
	{
		$joinpoint->setReturnedValue(
			Object_Builder::current()->replacementClassName($joinpoint->getReturnedValue())
		);
	}

	//----------------------------------------------------------------- onShortClassNamePropertyWrite
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function onShortClassNamePropertyWrite(AopJoinpoint $joinpoint)
	{
		Object_Builder::onClassNamePropertyWrite($joinpoint, false);
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::add("after",
			"write " . 'SAF\Framework\Controller_Uri->controller_name',
			array(__CLASS__, "onShortClassNamePropertyWrite")
		);
		Aop::add("after",
			'SAF\Framework\Set->elementClassNameOf()',
			array(__CLASS__, "onMethodWithReturnedValue")
		);
		Aop::add("before",
			'SAF\Framework\Getter->getCollection()',
			array(__CLASS__, "onMethodWithClassName1")
		);
		Aop::add("before",
			'SAF\Framework\Getter->getObject()',
			array(__CLASS__, "onMethodWithClassName1")
		);
		Aop::add("before",
			'SAF\Framework\Search_Object->newInstance()',
			array(__CLASS__, "onMethodWithClassName0")
		);
	}

	//-------------------------------------------------------------------------- replacementClassName
	/**
	 * Gets replacement class name for a parent class name
	 *
	 * @param $class_name string can be short or full class name
	 * @return string
	 */
	public function replacementClassName($class_name)
	{
		$class_name = Namespaces::fullClassName($class_name);
		$result = isset($this->new_classes[$class_name])
			? $this->new_classes[$class_name]
			: $class_name;
		return Namespaces::defaultFullClassName($result, $class_name);
	}

}
