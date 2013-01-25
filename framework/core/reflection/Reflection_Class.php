<?php
namespace SAF\Framework;
use ReflectionClass;
use ReflectionException;

require_once "framework/core/reflection/annotations/Annotation.php";
require_once "framework/core/reflection/annotations/Annotation_Parser.php";
require_once "framework/core/reflection/annotations/Annoted.php";
require_once "framework/core/reflection/Has_Doc_Comment.php";
require_once "framework/core/reflection/Reflection_Class_Properties_Access.php";
require_once "framework/core/reflection/Reflection_Method.php";
require_once "framework/core/reflection/Reflection_Property.php";

class Reflection_Class extends ReflectionClass implements Has_Doc_Comment
{
	use Annoted;

	//---------------------------------------------------------------------------------------- $cache
	/**
	 * Cache Reflection_Class objects for each class name
	 *
	 * @var Reflection_Class[]
	 */
	private static $cache = array();

	//---------------------------------------------------------------------------------- $doc_comment
	/**
	 * Cached value for the doc comment (set by getDocComment() only when $use is true)
	 *
	 * @var string
	 */
	private $doc_comment;

	//------------------------------------------------------------------------------ accessProperties
	/**
	 * Change all properties accessibility to true for a given class, and return all class and parents properties list
	 *
	 * Done for class properties and its parents.
	 * Accessibility should be set back with a call to done() after use.
	 * If class properties are set to accessible several times, they will become non-accessible after the same number of done() calls.
	 *
	 * @return Reflection_Property[]
	 */
	public function accessProperties()
	{
		return Reflection_Class_Properties_Access::access($this);
	}

	//-------------------------------------------------------------------------- accessPropertiesDone
	/**
	 * All private class and parents properties go back to private
	 *
	 * This must be called after the properties used with access() are no longer needed as accessible.
	 * If more than one access() has been called for the class, the release will be done only on the last done() access.
	 */
	public function accessPropertiesDone()
	{
		Reflection_Class_Properties_Access::done($this);
	}

	//------------------------------------------------------------------------------ getAllProperties
	/**
	 * Get all properties from a class and its parents
	 *
	 * If a property overrides a parent property, parent AND child properties will be listed (only if $by_name keeps false).
	 * If $by_name is set to true, result array will be indiced by names. With this option parent properties will be replace by overriden child properties.
	 *
	 * @param $filter string
	 * @param $by_name boolean
	 * @return Reflection_Property[]
	 */
	public function getAllProperties($filter = Reflection_Property::ALL, $by_name = true)
	{
		$parent = $this->getParentClass();
		if ($parent) {
			$properties = array_merge(
				$parent->getAllProperties($filter, $by_name), $this->getProperties($filter, $by_name)
			);
		}
		else {
			$properties = $this->getProperties($filter, $by_name);
		}
		return $properties;
	}

	//-------------------------------------------------------------------------------- getConstructor
	/**
	 * Gets the constructor of the reflected class
	 *
	 * @return Reflection_Method
	 */
	public function getConstructor()
	{
		$constructor = parent::getConstructor();
		return $constructor ? Reflection_Method::getInstanceOf($constructor) : $constructor;
	}

	//--------------------------------------------------------------------------------- getDocComment
	/**
	 * Return doc comment of the class
	 *
	 * @param $get_parents boolean if true, get doc comment of parent classes too
	 * @return string
	 */
	public function getDocComment($get_parents = false)
	{
		if (!$get_parents) {
			return parent::getDocComment();
		}
		if (!is_string($this->doc_comment)) {
			$this->doc_comment = parent::getDocComment();
			if ($parent_class = $this->getParentClass()) {
				$this->doc_comment .= $parent_class->getDocComment(true);
			}
			foreach ($this->getInterfaces() as $interface) {
				$this->doc_comment .= $interface->getDocComment(true);
			}
			foreach ($this->getTraits() as $trait) {
				$this->doc_comment .= $trait->getDocComment(true);
			}
		}
		return $this->doc_comment;
	}

	//--------------------------------------------------------------------------------- getInstanceOf
	/**
	 * Return Reflection_Class instance for a class name, an object or a php ReflectionClass object
	 *
	 * @param $of_class string|object|ReflectionClass|Type
	 * @return Reflection_Class
	 */
	public static function getInstanceOf($of_class)
	{
		if ($of_class instanceof Type) {
			$of_class = $of_class->asString();
		}
		elseif ($of_class instanceof ReflectionClass) {
			$of_class = $of_class->name;
		}
		elseif (is_object($of_class)) {
			$of_class = get_class($of_class);
		}
		if (isset(self::$cache[$of_class])) {
			$class = self::$cache[$of_class];
		}
		else {
			try {
				$class = new Reflection_Class($of_class);
			}
			catch (ReflectionException $e) {
				$class = new Reflection_Class(Namespaces::fullClassName($of_class));
			}
			self::$cache[$of_class] = $class;
		}
		return $class;
	}

	//--------------------------------------------------------------------------------- getInterfaces
	/**
	 * Gets interfaces
	 *
	 * @param $by_name boolean
	 * @return Reflection_Class[]
	 */
	public function getInterfaces($by_name = true)
	{
		$interfaces = array();
		foreach (parent::getInterfaces() as $key => $interface) {
			$interfaces[$by_name ? $interface->name : $key] = Reflection_Class::getInstanceOf($interface);
		}
		return $interfaces;
	}

	//------------------------------------------------------------------------------------- getMethod
	/**
	 * Gets a Reflection_Method for a class method
	 *
	 * Only a method of current class can be retrieved, not one from parent classes or traits.
	 *
	 * @param string
	 * @return Reflection_Method
	 */
	public function getMethod($method_name)
	{
		$method = parent::getMethod($method_name);
		return $method ? Reflection_Method::getInstanceOf($method) : $method;
	}

	//------------------------------------------------------------------------------------ getMethods
	/**
	 * Gets an array of methods for the class
	 *
	 * Only methods visible for current class are retrieved, not the privates ones from parents or traits.
	 *
	 * @param $filter string any combination of Reflection_Method::IS_* constants
	 * @param $by_name boolean if true, only the last override of each method name will be kept
	 * @return Reflection_Method[] indice is the method name if $by_name is true, else this will be an integer
	 */
	public function getMethods($filter = Reflection_Method::ALL, $by_name = true)
	{
		$methods = array();
		$meths = parent::getMethods($filter);
		foreach ($meths as $key => $method) {
			$methods[$by_name ? $method->name : $key] = Reflection_Method::getInstanceOf($method);
		}
		return $methods;
	}

	//-------------------------------------------------------------------------------- getParentClass
	/**
	 * Gets parent class
	 *
	 * @return Reflection_Class
	 */
	public function getParentClass()
	{
		$parent_class = parent::getParentClass();
		return $parent_class ? static::getInstanceOf($parent_class) : $parent_class;
	}

	//--------------------------------------------------------------------------------- getProperties
	/**
	 * Gets an array of properties for the class
	 *
	 * Properties visible for current class, not the privates ones from parents and traits are retrieved.
	 *
	 * @param $filter string any combination of Reflection_Property::IS_* constants
	 * @param $by_name boolean if true, only the last override of each property will be kept
	 * @return Reflection_Property[] indice is the property name if $by_name is true, else this will be an integer
	 */
	public function getProperties($filter = Reflection_Property::ALL, $by_name = true)
	{
		$properties = array();
		foreach (parent::getProperties($filter) as $key => $property) {
			$properties[$by_name ? $property->name : $key] = Reflection_Property::getInstanceOf($property);
		}
		return $properties;
	}

	//----------------------------------------------------------------------------------- getProperty
	/**
	 * Retrieves reflected properties
	 *
	 * Only a property visible for current class can be retrieved, not the privates ones from parent classes or traits.
	 *
	 * @param string
	 * @return Reflection_Property
	 */
	public function getProperty($name)
	{
		$property = parent::getProperty($name);
		return $property ? Reflection_Property::getInstanceOf($property) : $property;
	}

	//------------------------------------------------------------------------------------- getTraits
	/**
	 * Gets traits
	 *
	 * @param $by_name boolean
	 * @return Reflection_Class[]
	 */
	public function getTraits($by_name = true)
	{
		$traits = array();
		foreach (parent::getTraits() as $key => $trait) {
			$traits[$by_name ? $trait->name : $key] = Reflection_Class::getInstanceOf($trait);
		}
		return $traits;
	}

}
