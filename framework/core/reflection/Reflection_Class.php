<?php
namespace SAF\Framework;

use ReflectionClass;

/**
 * A rich extension of the PHP ReflectionClass class, adding :
 * - properties access methods to write data into private properties
 * - access to the full list of properties and parent properties
 * - annotations management
 *
 * @todo store it into an independent SAF\Reflection package
 */
class Reflection_Class extends ReflectionClass implements Has_Doc_Comment
{
	use Annoted;

	//---------------------------------------------------------------------------------- $doc_comment
	/**
	 * Cached value for the doc comment (set by getDocComment() only when $use is true)
	 *
	 * @var string
	 */
	private $doc_comment;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string The name of the class
	 */
	public function __toString()
	{
		return $this->name;
	}

	//------------------------------------------------------------------------------ accessProperties
	/**
	 * Change all properties accessibility to true for a given class, and return all class and parents
	 * properties list
	 *
	 * Done for class properties and its parents.
	 * Accessibility should be set back with a call to done() after use.
	 * If class properties are set to accessible several times, they will become non-accessible after
	 * the same number of done() calls.
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
	 * If more than one access() has been called for the class, the release will be done only on the
	 * last done() access.
	 */
	public function accessPropertiesDone()
	{
		Reflection_Class_Properties_Access::done($this);
	}

	//------------------------------------------------------------------------------ getAllProperties
	/**
	 * Get all properties from a class and its parents
	 *
	 * If a property overrides a parent property, parent AND child properties will be listed (only if
	 * $by_name keeps false).
	 * If $by_name is set to true, result array keys will be names.
	 * With this option parent properties will be replace by overridden child properties.
	 *
	 * @deprecated
	 * @param $filter      integer|string
	 * @param $by_name     boolean
	 * @param $final_class string
	 * @return Reflection_Property[]
	 * @todo remove this : getProperties() gets all properties (tested with PHP 5.5.3)
	 */
	public function getAllProperties(
		$filter = Reflection_Property::ALL, $by_name = true, $final_class = null
	) {
		$parent = $this->getParentClass();
		if ($parent) {
			$properties = array_merge(
				$parent->getAllProperties($filter, $by_name, $final_class),
				$this->getProperties($filter, $by_name, $final_class)
			);
		}
		else {
			$properties = $this->getProperties($filter, $by_name, $final_class);
		}
		return $properties;
	}

	//------------------------------------------------------------------------ getAnnotationCachePath
	/**
	 * @return string[]
	 */
	protected function getAnnotationCachePath()
	{
		return array($this->name, "@");
	}

	//-------------------------------------------------------------------------- getAnnotedProperties
	/**
	 * Gets all properties which annotation has given value (or are not empty, if value is not set)
	 *
	 * @param $annotation_name  string
	 * @param $annotation_value mixed
	 * @return Reflection_Property[]
	 */
	public function getAnnotedProperties($annotation_name, $annotation_value = null)
	{
		$properties = array();
		foreach ($this->getAllProperties() as $property) {
			$annotation = $property->getAnnotation($annotation_name);
			if (
				(isset($annotation_value) && ($annotation->value == $annotation_value))
				|| (!isset($annotation_value) && !empty($annotation->value))
			) {
				$properties[] = $property;
			}
		}
		return $properties;
	}

	//---------------------------------------------------------------------------- getAnnotedProperty
	/**
	 * Gets higher level property which annotation has given value (or is not empty, if value is not
	 * set)
	 *
	 * @param $annotation_name  string
	 * @param $annotation_value mixed
	 * @return Reflection_Property|null
	 */
	public function getAnnotedProperty($annotation_name, $annotation_value = null)
	{
		/** @var $property Reflection_Property */
		foreach (array_reverse($this->getAllProperties()) as $property) {
			$annotation = $property->getAnnotation($annotation_name);
			if (
				(isset($annotation_value) && ($annotation->value == $annotation_value))
				|| (!isset($annotation_value) && !empty($annotation->value))
			) {
				return $property;
			}
		}
		return null;
	}

	//------------------------------------------------------------------ getDeclaredClassesUsingTrait
	/**
	 * Returns a list of declared classes that use this trait
	 *
	 * Note : child classes of a class using a trait will not be listed here
	 *
	 * @return array
	 */
	public function getDeclaredClassesUsingTrait()
	{
		$classes = array();
		foreach (get_declared_classes() as $class_name) {
			if (in_array($this->name, class_uses($class_name))) {
				$classes[$class_name] = new Reflection_Class($class_name);
			}
		}
		return $classes;
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
		return $constructor
			? new Reflection_Method($constructor->class, $constructor->name) : $constructor;
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
			foreach ($this->getTraits() as $trait) {
				$this->doc_comment .= $trait->getDocComment(true);
			}
			foreach ($this->getInterfaces() as $interface) {
				$this->doc_comment .= $interface->getDocComment(true);
			}
		}
		return $this->doc_comment;
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
			$interfaces[$by_name ? $interface->name : $key] = new Reflection_Class($interface->name);
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
		return $method ? new Reflection_Method($method->class, $method->name) : $method;
	}

	//------------------------------------------------------------------------------------ getMethods
	/**
	 * Gets an array of methods for the class
	 *
	 * Only methods visible for current class are retrieved, not the privates ones from parents or
	 * traits.
	 *
	 * @param $filter int|null|string any combination of Reflection_Method::IS_* constants
	 * @param $by_name boolean if true, only the last override of each method name will be kept
	 * @return Reflection_Method[] indice is the method name if $by_name is true, else this will be an
	 * integer
	 */
	public function getMethods($filter = Reflection_Method::ALL, $by_name = true)
	{
		$methods = array();
		$meths = parent::getMethods($filter);
		foreach ($meths as $key => $method) {
			$methods[$by_name ? $method->name : $key] = new Reflection_Method(
				$method->class, $method->name
			);
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
		return $parent_class ? new Reflection_Class($parent_class->name) : $parent_class;
	}

	//--------------------------------------------------------------------------------- getProperties
	/**
	 * Gets an array of properties for the class
	 *
	 * Properties visible for current class, not the privates ones from parents and traits are
	 * retrieved.
	 *
	 * @param $filter      integer|string any combination of Reflection_Property::IS_* constants
	 * @param $by_name     boolean if true, only the last override of each property will be kept
	 * @param $final_class string
	 * @return Reflection_Property[] indice is the property name if $by_name is true, else this will
	 * be an integer
	 */
	public function getProperties(
		$filter = Reflection_Property::ALL, $by_name = true, $final_class = null
	) {
		if (!isset($final_class)) {
			$final_class = $this->name;
		}
		$properties = array();
		foreach (parent::getProperties($filter) as $key => $property) {
			$properties[$by_name ? $property->name : $key]
				= new Reflection_Property($final_class, $property->name);
		}
		return $properties;
	}

	//----------------------------------------------------------------------------------- getProperty
	/**
	 * Retrieves reflected properties
	 *
	 * Only a property visible for current class can be retrieved, not the privates ones from parent
	 * classes or traits.
	 *
	 * @param string
	 * @return Reflection_Property
	 */
	public function getProperty($name)
	{
		$property = property_exists($this->name, $name) ? parent::getProperty($name) : null;
		return $property ? new Reflection_Property($property->class, $property->name) : $property;
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
			$traits[$by_name ? $trait->name : $key] = new Reflection_Class($trait->name);
		}
		return $traits;
	}

}
