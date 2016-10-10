<?php
namespace SAF\Framework\Reflection;

use ReflectionClass;
use SAF\Framework\Reflection\Annotation\Annoted;
use SAF\Framework\Reflection\Annotation\Class_\Display_Order_Annotation;
use SAF\Framework\Reflection\Annotation\Parser;
use SAF\Framework\Reflection\Interfaces;
use SAF\Framework\Reflection\Interfaces\Has_Doc_Comment;
use SAF\Framework\Tools\Stringable;

/**
 * A rich extension of the PHP ReflectionClass class, adding :
 * - properties access methods to write data into private properties
 * - access to the full list of properties and parent properties
 * - annotations management
 */
class Reflection_Class extends ReflectionClass
	implements Has_Doc_Comment, Interfaces\Reflection_Class, Stringable
{
	use Annoted;

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
		$properties = $this->getProperties([T_EXTENDS, T_USE]);
		foreach ($properties as $property) {
			if (!$property->isPublic()) {
				$property->setAccessible(true);
			}
		}
		return $properties;
	}

	//-------------------------------------------------------------------------- accessPropertiesDone
	/**
	 * Reset properties accessibility to false when they are not public
	 *
	 * @return Reflection_Property[]
	 */
	public function accessPropertiesDone()
	{
		$properties = $this->getProperties([T_EXTENDS, T_USE]);
		foreach ($properties as $property) {
			if (!$property->isPublic()) {
				$property->setAccessible(false);
			}
		}
		return $properties;
	}

	//------------------------------------------------------------------------------------ fromString
	/**
	 * @param $string string
	 * @return self
	 */
	public static function fromString($string)
	{
		return new Reflection_Class($string);
	}

	//------------------------------------------------------------------------ getAnnotationCachePath
	/**
	 * @return string[]
	 */
	protected function getAnnotationCachePath()
	{
		return [$this->name, AT];
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
		$properties = [];
		foreach ($this->getProperties([T_EXTENDS, T_USE]) as $property) {
			$annotation = $property->getAnnotation($annotation_name);
			if (
				(isset($annotation_value) && ($annotation->value == $annotation_value))
				|| (!isset($annotation_value) && !empty($annotation->value))
			) {
				$properties[$property->name] = $property;
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
		foreach (array_reverse($this->getProperties([T_EXTENDS, T_USE])) as $property) {
			/** @var $property Reflection_Property */
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

	//-------------------------------------------------------------------------------- getConstructor
	/**
	 * Gets the constructor of the reflected class
	 *
	 * @return Reflection_Method
	 */
	public function getConstructor()
	{
		$constructor = parent::getConstructor();
		return $constructor ? new Reflection_Method($this->name, $constructor->name) : null;
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
		$classes = [];
		foreach (get_declared_classes() as $class_name) {
			if (in_array($this->name, class_uses($class_name))) {
				$classes[$class_name] = new Reflection_Class($class_name);
			}
		}
		return $classes;
	}

	//-------------------------------------------------------------------------- getDefaultProperties
	/**
	 * Gets default value of properties
	 *
	 * @param $flags integer[] T_EXTENDS, T_USE
	 * @return array
	 */
	public function getDefaultProperties($flags = [])
	{
		$defaults = parent::getDefaultProperties();
		if ($flags) {
			if (in_array(T_EXTENDS, $flags)) {
				$parent = $this->getParentClass();
				while ($parent) {
					$defaults = array_merge($parent->getDefaultProperties(), $defaults);
					$parent = $parent->getParentClass();
				}
			}
		}
		return $defaults;
	}

	//--------------------------------------------------------------------------------- getDocComment
	/**
	 * Return doc comment of the class
	 *
	 * @param $flags integer[] T_EXTENDS, T_IMPLEMENTS, T_USE
	 * @return string
	 */
	public function getDocComment($flags = [])
	{
		$doc_comment = parent::getDocComment();
		if ($flags) {
			$flip = array_flip($flags);
			$doc_comment = parent::getDocComment();
			if (isset($flip[T_USE]) && !$this->isInterface()) {
				foreach ($this->getTraits() as $trait) {
					$doc_comment .= LF . Parser::DOC_COMMENT_IN . $trait->name . LF;
					$doc_comment .= $trait->getDocComment($flags);
				}
			}
			if (isset($flip[T_EXTENDS]) && !$this->isTrait()) {
				if ($parent_class = $this->getParentClass()) {
					$doc_comment .= LF . Parser::DOC_COMMENT_IN . $parent_class->name . LF;
					$doc_comment .= $parent_class->getDocComment($flags);
				}
			}
			if (isset($flip[T_IMPLEMENTS]) && !$this->isTrait()) {
				foreach ($this->getInterfaces() as $interface) {
					$doc_comment .= LF . Parser::DOC_COMMENT_IN . $interface->name . LF;
					$doc_comment .= $interface->getDocComment($flags);
				}
			}
		}
		return $doc_comment;
	}

	//--------------------------------------------------------------------------------- getInterfaces
	/**
	 * Gets interfaces
	 *
	 * @return Reflection_Class[]
	 */
	public function getInterfaces()
	{
		$interfaces = [];
		foreach (parent::getInterfaces() as $interface) {
			$interfaces[$interface->name] = new Reflection_Class($interface->name);
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
		return $method ? new Reflection_Method($this->name, $method->name) : null;
	}

	//------------------------------------------------------------------------------------ getMethods
	/**
	 * Gets an array of methods for the class
	 *
	 * Only methods visible for current class are retrieved, not the privates ones from parents or
	 * traits. If you set flags, this will override this limitation.
	 *
	 * @param $flags integer[] T_EXTENDS, T_IMPLEMENTS, T_USE
	 * @return Reflection_Method[] key is the name of the method
	 */
	public function getMethods($flags = [])
	{
		$methods = [];
		foreach (parent::getMethods() as $method) {
			$methods[$method->name] = new Reflection_Method($this->name, $method->name);
		}
		if ($flags) {
			$flip = array_flip($flags);
			if (isset($flip[T_USE])) {
				if (!isset($this->traits_methods)) {
					$this->traits_methods = [];
					foreach ($this->getTraits() as $trait) {
						$this->traits_methods = array_merge($trait->getMethods([T_USE]), $this->traits_methods);
					}
				}
				$methods = array_merge($this->traits_methods, $methods);
			}
			if (isset($flip[T_EXTENDS])) {
				if (!isset($this->parent_methods)) {
					$this->parent_methods = [];
					if ($parent = $this->getParentClass()) {
						$this->parent_methods = $parent->getMethods($flags);
					}
				}
				$methods = array_merge($this->parent_methods, $methods);
			}
			if (isset($flip[T_IMPLEMENTS])) {
				if (!isset($this->interfaces_methods)) {
					$this->interfaces_methods = [];
					foreach ($this->getInterfaces() as $interface) {
						$this->interfaces_methods = array_merge(
							$interface->getMethods($flags), $this->interfaces_methods
						);
					}
				}
				$methods = array_merge($this->interfaces_methods, $methods);
			}
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
		return $parent_class ? new Reflection_Class($parent_class->name) : null;
	}

	//--------------------------------------------------------------------------------- getProperties
	/**
	 * Gets an array of properties for the class
	 *
	 * Properties visible for current class, not the privates ones from parents and traits are
	 * retrieved.
	 * If you set self::T_SORT properties will be sorted by (@)display_order class annotation
	 *
	 * @param $flags integer[] T_EXTENDS, T_USE, self::T_SORT. Note: T_USE has no effect
	 * @param $final_class string force the final class to this name (mostly for internal use)
	 * @return Reflection_Property[] key is the name of the property
	 */
	public function getProperties($flags = [], $final_class = null)
	{
		if (!isset($final_class)) {
			$final_class = $this->name;
		}
		$properties = [];
		foreach (parent::getProperties() as $property) {
			$property = new Reflection_Property($this->name, $property->name);
			$property->final_class = $final_class;
			$properties[$property->name] = $property;
		}
		if (in_array(T_EXTENDS, $flags)) {
			$parent = $this->getParentClass();
			while ($parent) {
				$properties = array_merge($parent->getProperties([], $final_class), $properties);
				$parent = $parent->getParentClass();
			}
		}
		if (in_array(self::T_SORT, $flags)) {
			$properties = $this->sortProperties($properties);
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
	 * @param $name string The name of the property to get
	 * @return Reflection_Property
	 */
	public function getProperty($name)
	{
		$property = property_exists($this->name, $name) ? parent::getProperty($name) : null;
		return $property ? new Reflection_Property($this->name, $property->name) : null;
	}

	//------------------------------------------------------------------------------------- getTraits
	/**
	 * Gets traits
	 *
	 * @return Reflection_Class[]
	 */
	public function getTraits()
	{
		$traits = [];
		foreach (parent::getTraits() as $trait) {
			$traits[$trait->name] = new Reflection_Class($trait->name);
		}
		return $traits;
	}

	//------------------------------------------------------------------------------------------- isA
	/**
	 * Returns true if the class has $name into its parents, interfaces or traits
	 *
	 * @param $name  string
	 * @param $flags integer[] T_EXTENDS, T_IMPLEMENTS, T_USE
	 * @return boolean
	 */
	public function isA($name, $flags = [])
	{
		if ($flags) {
			$flip = array_flip($flags);
			if (isset($flip[T_USE]) && trait_exists($name)) {
				return isA($this->name, $name);
			}
			elseif (
				(isset($flip[T_EXTENDS]) && class_exists($name))
				|| (isset($flip[T_IMPLEMENTS]) && interface_exists($name))
			) {
				return is_a($this->name, $name, true);
			}
		}
		return false;
	}

	//------------------------------------------------------------------------------------ isAbstract
	/**
	 * PHP's ReflectionClass behaviour for this is :
	 * - Abstract classes are abstract (this is the main use)
	 * - Interfaces are not abstract
	 * - PHP before 7.0 : Traits are abstract. PHP 7.0 and next : Traits are not abstract.
	 *
	 * Reflection_Class behaviour will be :
	 * - Abstract classes, Interfaces and Traits are always abstract
	 *
	 * @return boolean
	 */
	public function isAbstract()
	{
		return parent::isAbstract() || $this->isInterface() || $this->isTrait();
	}

	//-------------------------------------------------------------------------------- sortProperties
	/**
	 * Sort the properties list from @display_order class annotation(s)
	 *
	 * @param $properties Reflection_Property[] key is the name of the property
	 * @return Reflection_Property[] key is the name of the property
	 */
	public function sortProperties(array $properties)
	{
		/** @var $annotations Display_Order_Annotation[] */
		if ($annotations = $this->getListAnnotations(Display_Order_Annotation::ANNOTATION)) {
			$sorted_properties = [];
			foreach ($annotations as $annotation) {
				foreach ($annotation->values() as $property_name) {
					if (isset($properties[$property_name])) {
						$sorted_properties[$property_name] = $properties[$property_name];
					}
				}
			}
			foreach ($properties as $property_name => $property) {
				if (!isset($sorted_properties[$property_name])) {
					$sorted_properties[$property_name] = $property;
				}
			}
			return $sorted_properties;
		}
		return $properties;
	}

}
