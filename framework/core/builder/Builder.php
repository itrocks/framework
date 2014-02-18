<?php
namespace SAF\Framework;

use ReflectionClass;
use SAF\Framework\Builder\Compiler;
use SAF\Plugins;
use Serializable;

/**
 * The Builder plugin replaces 'new Class_Name' calls by 'Builder::create('Class_Name')' in order to
 * enable objects substitution
 *
 * You should always use these calls for SAF business objects instantiations.
 *
 * @todo remove dependencies
 */
class Builder implements Plugins\Activable, Plugins\Registerable, Serializable
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

	//-------------------------------------------------------------------------------------- activate
	public function activate()
	{
		self::current($this);
	}

	//------------------------------------------------------------------ afterNamespacesFullClassName
	/**
	 * @param $short_class_name string
	 * @param $result           string
	 * @return string
	 */
	public static function afterNamespacesFullClassName($short_class_name, $result)
	{
		return (Namespaces::isShortClassName($short_class_name))
			? Builder::current()->replacementClassName($result)
			: $result;
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
	 * @param $args       mixed[]|mixed some arguments into an array, or a single non-array argument
	 * @return object
	 */
	public static function create($class_name, $args = null)
	{
		return isset($args)
			? self::current()->newInstanceArgs($class_name, is_array($args) ? $args : array($args))
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

	//------------------------------------------------------------------------------------- fromArray
	/**
	 * Changes an array into an object
	 *
	 * You should set only public and non-static properties values
	 *
	 * @param $class_name string
	 * @param $array      array
	 * @return object
	 */
	public static function fromArray($class_name, $array)
	{
		$object = self::create($class_name);
		if (isset($array)) {
			foreach ($array as $property_name => $value) {
				if (is_array($value)) {
					$property = new Reflection_Property($class_name, $property_name);
					if ($property->getType()->isClass()) {
						$property_class_name = $property->getType()->getElementTypeAsString();
						if ($property->getType()->isMultiple()) {
							foreach ($value as $key => $val) {
								$value[$key] = self::fromArray($property_class_name, $val);
							}
						}
						else {
							$value = self::fromArray($property_class_name, $value);
						}
						$property->setValue($object, $value);
					}
				}
				$object->$property_name = $value;
			}
		}
		return $object;
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
		$class = new Reflection_Class(get_class($object));
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
		$class_name = $this->replacementClassName($class_name);
		return (new ReflectionClass($class_name))->newInstanceArgs($args);
	}

	//------------------------------------------------------------------------- onMethodWithClassName
	/**
	 * @param $class_name string
	 */
	public function onMethodWithClassName(&$class_name)
	{
		$class_name = $this->replacementClassName($class_name);
	}

	//----------------------------------------------------------------------- onMethodWithReturnValue
	/**
	 * @param $result string
	 * @return string
	 */
	public function onMethodWithReturnedValue($result)
	{
		return $this->replacementClassName($result);
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Plugins\Register
	 */
	public function register(Plugins\Register $register)
	{
		$aop = $register->aop;
		$this->replacements = (new Compiler())->compile($this->replacements);
		$aop->beforeMethod(
			array(Getter::class, 'getCollection'),
			array($this, 'onMethodWithClassName')
		);
		$aop->beforeMethod(
			array(Getter::class, 'getObject'),
			array($this, 'onMethodWithClassName')
		);
		$aop->afterMethod(
			array(Namespaces::class, 'fullClassName'),
			array($this, 'afterNamespacesFullClassName')
		);
		$aop->beforeMethod(
			array(Search_Object::class, 'create'),
			array($this, 'onMethodWithClassName')
		);
		$aop->afterMethod(
			array(Set::class, 'elementClassNameOf'),
			array($this, 'onMethodWithReturnedValue')
		);
		$aop->afterMethod(
			array(Sql_Joins::class, 'addSimpleJoin'),
			array($this, 'onMethodWithReturnedValue')
		);
		// TODO this is really slow : hardcode it and optimize it in C
		//set_new_overload(function($class_name) { return Builder::className($class_name); });
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
		if (is_array($result)) {
			$result = Class_Builder::build($class_name, $result);
			$this->replacements[$class_name] = $result;
		}
		return $result;
	}

	//------------------------------------------------------------------------------------- serialize
	/**
	 * @return string the string representation of the object or null
	 */
	public function serialize()
	{
		return serialize($this->replacements);
	}

	//-------------------------------------------------------------------------------- setReplacement
	/**
	 * Sets a new replacement
	 *
	 * Returns the hole replacement class name as you can set it back at will
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

	//------------------------------------------------------------------------------- sourceClassName
	/**
	 * Gets source class name for a replacement class name
	 *
	 * @param $class_name
	 */
	public function sourceClassName($class_name)
	{
		return array_search($class_name, $this->replacements) ?: $class_name;
	}

	//----------------------------------------------------------------------------------- unserialize
	/**
	 * @param $serialized string
	 */
	public function unserialize($serialized)
	{
		$this->replacements = unserialize($serialized);
	}

}
