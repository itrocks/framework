<?php
namespace SAF\Framework;
use ReflectionClass;
use ReflectionException;

require_once "framework/classes/reflection/annotations/Annotation.php";
require_once "framework/classes/reflection/annotations/Annotation_Parser.php";
require_once "framework/classes/reflection/annotations/Annoted.php";
require_once "framework/classes/reflection/Reflection_Class_Properties_Access.php";
require_once "framework/classes/reflection/Reflection_Method.php";
require_once "framework/classes/reflection/Reflection_Property.php";

class Reflection_Class extends ReflectionClass implements Annoted
{

	//---------------------------------------------------------------------------------------- $cache
	/**
	 * Cache Reflection_Class objects for each class name
	 *
	 * @var multitype:Reflection_Class
	 */
	private static $cache = array();

	//-------------------------------------------------------------------------------------- $dataset
	/**
	 * Cached value for the @dataset annotation value
	 *
	 * @var string
	 */
	private $dataset;

	//------------------------------------------------------------------------------------------ $use
	/**
	 * Cached values for the @use annotations values
	 *
	 * @var multitype:string
	 */
	private $use;

	//------------------------------------------------------------------------------ accessProperties
	/**
	 * Change all properties accessibility to true for a given class, and return all class and parents properties list
	 *
	 * Done for class properties and its parents.
	 * Accessibility should be set back with a call to done() after use.
	 * If class properties are set to accessible several times, they will become non-accessible after the same number of done() calls.
	 *
	 * @param Reflection_Class $object_class
	 * @return multitype:Reflection_Property
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
	 *
	 * @param Reflection_Class $object_class
	 */
	public function accessPropertiesDone()
	{
		return Reflection_Class_Properties_Access::done($this);
	}

	//------------------------------------------------------------------------------ getAllProperties
	/**
	 * Get all properties from a class and its parents
	 *
	 * If a property overrides a parent property, parent AND child properties will be listed (only if $by_name keeps false).
	 * If $by_name is set to true, result array will be indiced by names. With this option parent properties will be replace by overriden child properties.
	 *
	 * @param string  $filter
	 * @param boolean $by_name
	 * @return multitype:Reflection_Property
	 */
	public function getAllProperties($filter = Reflection_Property::ALL, $by_name = true)
	{
		$parent = $this->getParentClass();
		if ($parent) {
			$properties = array_merge(
				$parent->getAllProperties($filter, $by_name), $this->getProperties($filter)
			);
		}
		else {
			$properties = $this->getProperties($filter, $by_name);
		}
		return $properties;
	}

	//--------------------------------------------------------------------------------- getAnnotation
	/**
	 * Gets an annotation of the reflected class
	 *
	 * @return Annotation
	 */
	public function getAnnotation($annotation_name)
	{
		return Annotation_Parser::byName($this->getDocComment(), $annotation_name);
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

	//------------------------------------------------------------------------------------ getDataset
	/**
	 * Gets the dataset annotation value
	 *
	 * @return string
	 */
	public function getDataset()
	{
		if (!is_string($this->dataset)) {
			$annotation = $this->getAnnotation("dataset");
			$this->dataset = $annotation ? $annotation->value : "";
		}
		return $this->dataset;
	}

	//--------------------------------------------------------------------------------- getInstanceOf
	/**
	 * Return Reflection_Class instance for a class name, an object or a php ReflectionClass object
	 *
	 * @param string | object | ReflectionClass $of_class
	 * @return Reflection_Class
	 */
	public static function getInstanceOf($of_class)
	{
		if ($of_class instanceof ReflectionClass) {
			$of_class = $of_class->name;
		}
		elseif (is_object($of_class)) {
			$of_class = get_class($of_class);
		}
		if (isset(Reflection_Class::$cache[$of_class])) {
			$class = Reflection_Class::$cache[$of_class];
		}
		else {
			try {
				$class = new Reflection_Class($of_class);
			}
			catch (ReflectionException $e) {
				$class = new Reflection_Class(Namespaces::fullClassName($of_class));
			}
			Reflection_Class::$cache[$of_class] = $class;
		}
		return $class;
	}

	//------------------------------------------------------------------------------------- getMethod
	/**
	 * Gets a Reflection_Method for a class method
	 *
	 * Only a method of current class can be retrieved, not one from parent classes or traits.
	 *
	 * @return Reflection_Method
	 */
	public function getMethod($name)
	{
		$method = parent::getMethod($name);
		return $method ? Reflection_Method::getInstanceOf($method) : $method;
	}

	//------------------------------------------------------------------------------------ getMethods
	/**
	 * Gets an array of methods for the class
	 *
	 * Only methods of current class are retrieved, not those from parents or traits.
	 *
	 * @return multitype:Reflection_Method
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
		return $parent_class ? Reflection_Class::getInstanceOf($parent_class) : $parent_class;
	}

	//--------------------------------------------------------------------------------- getProperties
	/**
	 * Gets an array of properties for the class
	 *
	 * Properties for current class, parents and traits are retrieved.
	 *
	 * @return multitype:Reflection_Property
	 */
	public function getProperties($filter = Reflection_Property::ALL, $by_name = true)
	{
		$properties = array();
		$props = parent::getProperties($filter);
		foreach ($props as $key => $property) {
			$properties[$by_name ? $property->name : $key] = Reflection_Property::getInstanceOf($property);
		}
		return $properties;
	}

	//----------------------------------------------------------------------------------- getProperty
	/**
	 * Retrieves reflected properties
	 *
	 * Only a property of current class can be retrieved, not one from parent classes or traits.
	 *
	 * @return Reflection_Property
	 */
	public function getProperty($name)
	{
		$property = parent::getProperty($name);
		return $property ? Reflection_Property::getInstanceOf($property) : $property;
	}

	//---------------------------------------------------------------------------------------- getUse
	/**
	 * Gets the @use annotation value
	 *
	 * This annotation defines the list of fields which annotations must be read into the current class.
	 * This is used for trait herited fields, as they can't be overridden and new phpdocs are ignored.
	 *
	 * @return multitype:string
	 */
	public function getUse()
	{
		if (!is_array($this->use)) {
			$this->use = array();
			$annotations = $this->getAnnotation("use");
			if ($annotations) {
				foreach ($annotations as $annotation) {
					$this->use[] = $annotation->value;
				}
			} else {
				$this->use = array();
			}
		}
		return $this->use;
	}

}
