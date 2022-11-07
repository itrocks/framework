<?php
namespace ITRocks\Framework\Reflection;

use ITRocks\Framework\Reflection\Annotation\Annoted;
use ITRocks\Framework\Reflection\Annotation\Class_\Display_Order_Annotation;
use ITRocks\Framework\Reflection\Annotation\Parser;
use ITRocks\Framework\Reflection\Annotation\Property\Default_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\List_Annotation;
use ITRocks\Framework\Reflection\Interfaces;
use ITRocks\Framework\Reflection\Interfaces\Has_Doc_Comment;
use ITRocks\Framework\Tools\Stringable;
use ITRocks\Framework\Tools\Value_Lists;
use ReflectionClass;
use ReflectionException;
use ReturnTypeWillChange;

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
	public function __toString() : string
	{
		return $this->name;
	}

	//------------------------------------------------------------------------------------ fromString
	/**
	 * @param $string string
	 * @return static
	 * @throws ReflectionException
	 */
	public static function fromString(string $string) : static
	{
		return new Reflection_Class($string);
	}

	//------------------------------------------------------------------------ getAnnotationCachePath
	/**
	 * @return string[]
	 */
	protected function getAnnotationCachePath() : array
	{
		return [$this->name, AT];
	}

	//-------------------------------------------------------------------------- getAnnotedProperties
	/**
	 * Gets all properties which annotation has given value (or are not empty, if value is not set)
	 *
	 * @param $annotation_name  string
	 * @param $annotation_value mixed
	 * @param $flags            string[] private, protected, static, or public=visible=empty
	 * @return Reflection_Property[]
	 */
	public function getAnnotedProperties(
		string $annotation_name, mixed $annotation_value = null,
		array $flags = [Access::PRIVATE, Access::PROTECTED, Access::STATIC]
	) : array
	{
		$include_private   = in_array(Access::PRIVATE,   $flags);
		$include_protected = in_array(Access::PROTECTED, $flags);
		$include_static    = in_array(Access::STATIC,    $flags);

		$properties = [];
		foreach ($this->getProperties([T_EXTENDS, T_USE]) as $property) {
			if (
				(!$include_private      && $property->isPrivate())
				|| (!$include_protected && $property->isProtected())
				|| (!$include_static    && $property->isStatic())
			) {
				continue;
			}
			$annotation = $property->getAnnotation($annotation_name);
			if (
				(isset($annotation_value) && ($annotation->value === $annotation_value))
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
	 * @return ?Reflection_Property
	 */
	public function getAnnotedProperty(string $annotation_name, mixed $annotation_value = null)
		: ?Reflection_Property
	{
		foreach (array_reverse($this->getProperties([T_EXTENDS, T_USE])) as $property) {
			/** @var $property Reflection_Property */
			$annotation = $property->getAnnotation($annotation_name);
			if (
				(isset($annotation_value) && ($annotation->value === $annotation_value))
				|| (!isset($annotation_value) && !empty($annotation->value))
			) {
				return $property;
			}
		}
		return null;
	}

	//---------------------------------------------------------------------------------- getConstants
	/**
	 * Gets defined constants from a class
	 *
	 * TODO Problem with this implementation : if a interface/parent/trait constant is overridden in current class, this will remove it. No problem for [T_EXTENDS, T_USE] default use.
	 *
	 * @noinspection PhpParameterNameChangedDuringInheritanceInspection $flags @TODO proxify ?
	 * @param $flags integer[] T_EXTENDS, T_IMPLEMENTS, T_USE
	 * @return array Constant name in key, constant value in value
	 */
	public function getConstants(array|int|null $flags = [T_EXTENDS, T_IMPLEMENTS, T_USE]) : array
	{
		$constants = parent::getConstants();
		$flags     = array_flip($flags);
		if (!isset($flags[T_EXTENDS])) {
			if ($parent = $this->getParentClass()) {
				foreach (array_keys($parent->getConstants([T_EXTENDS, T_USE])) as $constant_name) {
					unset($constants[$constant_name]);
				}
			}
		}
		if (!isset($flags[T_IMPLEMENTS])) {
			foreach ($this->getInterfaces() as $interface) {
				foreach (array_keys($interface->getConstants([T_IMPLEMENTS])) as $constant_name) {
					unset($constants[$constant_name]);
				}
			}
		}
		if (!isset($flags[T_USE])) {
			foreach ($this->getTraits() as $trait) {
				foreach (array_keys($trait->getConstants([T_USE])) as $constant_name) {
					unset($constants[$constant_name]);
				}
			}
		}
		return $constants;
	}

	//-------------------------------------------------------------------------------- getConstructor
	/**
	 * Gets the constructor of the reflected class
	 *
	 * @noinspection PhpDocMissingThrowsInspection $constructor has been tested for existing
	 * @return ?Reflection_Method
	 */
	public function getConstructor() : ?Reflection_Method
	{
		$constructor = parent::getConstructor();
		/** @noinspection PhpUnhandledExceptionInspection $constructor has been tested for existing */
		return $constructor ? new Reflection_Method($this->name, $constructor->name) : null;
	}

	//------------------------------------------------------------------ getDeclaredClassesUsingTrait
	/**
	 * Returns a list of declared classes that use this trait
	 *
	 * Note : child classes of a class using a trait will not be listed here
	 *
	 * @noinspection PhpDocMissingThrowsInspection $class_name is a declared class
	 * @return Reflection_Class[]
	 */
	public function getDeclaredClassesUsingTrait() : array
	{
		$classes = [];
		foreach (get_declared_classes() as $class_name) {
			if (in_array($this->name, class_uses($class_name))) {
				/** @noinspection PhpUnhandledExceptionInspection $class_name is a declared class */
				$classes[$class_name] = new Reflection_Class($class_name);
			}
		}
		return $classes;
	}

	//-------------------------------------------------------------------------- getDefaultProperties
	/**
	 * Gets default value of properties
	 *
	 * Public and protected properties defaults are taken even without T_EXTENDS.
	 * Private properties defaults are taken only if you set T_EXTENDS to true.
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $flags          integer[] T_EXTENDS. T_USE is implicit
	 * @param $use_annotation boolean|string Set this false to disable interpretation of @default
	 *                        Set this to 'constant' to accept @default if @return_constant is set
	 * @param $property_name  string for optimization purpose : only get defaults for this property
	 * @return array
	 */
	public function getDefaultProperties(
		array $flags = [], bool $use_annotation = true, string $property_name = ''
	) : array
	{
		// list default values
		$defaults = parent::getDefaultProperties();
		if ($flags) {
			if (in_array(T_EXTENDS, $flags)) {
				$parent = $this->getParentClass();
				while ($parent && (!$property_name || !key_exists($property_name, $defaults))) {
					$defaults = array_merge(
						$parent->getDefaultProperties([], $use_annotation, $property_name),
						$defaults
					);
					$parent = $parent->getParentClass();
				}
			}
		}
		// if only one property asked
		if ($property_name) {
			$defaults = isset($defaults[$property_name])
				? [$property_name => $defaults[$property_name]]
				: [];
		}
		// scan for @default and use them
		if ($use_annotation) {
			foreach ($this->getProperties($flags) as $property) {
				if (
					($default_annotation = Default_Annotation::of($property))->value
					&& (
						($use_annotation !== 'constant')
						|| $default_annotation->getReflectionMethod()->getAnnotation('return_constant')->value
					)
				) {
					$defaults[$property->name] = $property->getDefaultValue($use_annotation, $default_object);
				}
			}
		}
		return $defaults;
	}

	//--------------------------------------------------------------------------------- getDocComment
	/**
	 * Accumulates documentations of parents and the class itself
	 *
	 * @param $flags   integer[]|null T_EXTENDS, T_IMPLEMENTS, T_USE
	 * @param $already boolean[] for internal use (recursion) : already got those classes (keys)
	 * @return string
	 */
	public function getDocComment(array|null $flags = [], array &$already = []) : string
	{
		$doc_comment = parent::getDocComment();
		if ($flags) {
			$flip = array_flip($flags);
			$doc_comment = parent::getDocComment();
			if (isset($flip[T_USE]) && !$this->isInterface()) {
				foreach ($this->getTraits() as $trait) {
					$doc_comment .= LF . Parser::DOC_COMMENT_IN . $trait->name . LF;
					$doc_comment .= $trait->getDocComment($flags, $already);
				}
			}
			if (isset($flip[T_EXTENDS]) && !$this->isTrait()) {
				if ($parent_class = $this->getParentClass()) {
					$doc_comment .= LF . Parser::DOC_COMMENT_IN . $parent_class->name . LF;
					$doc_comment .= $parent_class->getDocComment($flags, $already);
				}
			}
			if (isset($flip[T_IMPLEMENTS]) && !$this->isTrait()) {
				foreach ($this->getInterfaces() as $interface) {
					if (!isset($already[$interface->name])) {
						$already[$interface->name] = true;
						$doc_comment .= LF . Parser::DOC_COMMENT_IN . $interface->name . LF;
						$doc_comment .= $interface->getDocComment($flags, $already);
					}
				}
			}
		}
		return $doc_comment;
	}

	//--------------------------------------------------------------------------------- getInterfaces
	/**
	 * Gets interfaces
	 *
	 * @noinspection PhpDocMissingThrowsInspection $interface from parent::getInterfaces()
	 * @return Reflection_Class[]
	 */
	public function getInterfaces() : array
	{
		$interfaces = [];
		foreach (parent::getInterfaces() as $interface) {
			/** @noinspection PhpUnhandledExceptionInspection $interface from parent::getInterfaces() */
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
	 * @param $name string
	 * @return ?Reflection_Method
	 * @throws ReflectionException method does not exist
	 */
	#[ReturnTypeWillChange]
	public function getMethod(string $name) : ?Reflection_Method
	{
		$method = parent::getMethod($name);
		return new Reflection_Method($this->name, $method->name);
	}

	//------------------------------------------------------------------------------------ getMethods
	/**
	 * Gets an array of methods for the class
	 *
	 * Only methods visible for current class are retrieved, not the privates ones from parents or
	 * traits. If you set flags, this will override this limitation.
	 *
	 * @noinspection PhpParameterNameChangedDuringInheritanceInspection $flags @TODO proxify ?
	 * @noinspection PhpDocMissingThrowsInspection $method from parent::getMethods()
	 * @param $flags integer[]|null T_EXTENDS, T_IMPLEMENTS, T_USE
	 * @return Reflection_Method[] key is the name of the method
	 */
	public function getMethods(array|int $flags = null) : array
	{
		$methods = [];
		foreach (parent::getMethods() as $method) {
			/** @noinspection PhpUnhandledExceptionInspection $method from parent::getMethods() */
			$methods[$method->name] = new Reflection_Method($this->name, $method->name);
		}
		if ($flags) {
			/** @noinspection DuplicatedCode @TODO Factorize with a Reflection trait */
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

	//--------------------------------------------------------------------------------- getObjectVars
	/**
	 * Same as get_object_vars, but solves AOP properties and does not get hidden id* properties
	 *
	 * Get all object vars, but :
	 * - Remove _ AOP property
	 * - Remove id_ properties matching AOP properties
	 * - Remove property_name_ properties matching property_name AOP properties
	 *
	 * The result is a clean "object vars list" with only real properties declared into the class.
	 *
	 * @param $object object
	 * @param $aop    boolean if false, AOP is not applied and the actual values are get
	 * @return array
	 */
	public static function getObjectVars(object $object, bool $aop = true) : array
	{
		$vars = get_object_vars($object);
		if (isset($vars['_'])) {
			foreach (array_keys($vars['_']) as $property_name) {
				$vars[$property_name] = $aop ? $object->$property_name : $vars[$property_name . '_'];
				unset($vars['id_' . $property_name]);
				unset($vars[$property_name . '_']);
			}
			unset($vars['_']);
		}
		unset($vars['id']);
		return $vars;
	}

	//-------------------------------------------------------------------------------- getParentClass
	/**
	 * Gets parent class
	 *
	 * @noinspection PhpDocMissingThrowsInspection $parent_class from parent::getParentClass()
	 * @return ?Reflection_Class
	 */
	#[ReturnTypeWillChange]
	public function getParentClass() : ?Reflection_Class
	{
		$parent_class = parent::getParentClass();
		/** @noinspection PhpUnhandledExceptionInspection $parent_class from parent::getParentClass() */
		return $parent_class ? new Reflection_Class($parent_class->name) : null;
	}

	//---------------------------------------------------------------------------- getParentClassName
	/**
	 * Gets parent class name
	 *
	 * @return string
	 */
	public function getParentClassName() : string
	{
		$parent_class = parent::getParentClass();
		return $parent_class->name ?? '';
	}

	//--------------------------------------------------------------------------------- getProperties
	/**
	 * Gets an array of properties for the class
	 *
	 * Properties visible for current class, not the privates ones from parents and traits are
	 * retrieved.
	 * If you set self::T_SORT properties will be sorted by (@)display_order class annotation
	 *
	 * @noinspection PhpDocMissingThrowsInspection $property from parent::getProperties()
	 * @noinspection PhpParameterNameChangedDuringInheritanceInspection $flags @TODO Proxify ?
	 * @param $flags       integer[]|string[] Restriction. T_USE has no effect (always applied).
	 *                     flags @default [T_EXTENDS, T_USE] @values T_EXTENDS, T_USE, self::T_SORT
	 * @param $final_class ?string force the final class to this name (mostly for internal use)
	 * @param $visibility_flags integer|null filter parents visibility @values ReflectionProperty::const
	 * @return Reflection_Property[] key is the name of the property
	 */
	public function getProperties(
		array|int $flags = null, ?string $final_class = null, int $visibility_flags = null
	) : array
	{
		if (!isset($flags)) {
			$flags = [T_EXTENDS, T_USE];
		}
		if (!isset($final_class)) {
			$final_class = $this->name;
		}
		$extends               = in_array(T_EXTENDS, $flags);
		$properties            = [];
		$reflection_properties = $visibility_flags
			? parent::getProperties($visibility_flags)
			: parent::getProperties();
		foreach ($reflection_properties as $reflection_property) {
			if ($extends || ($reflection_property->class === $this->name)) {
				/** @noinspection PhpUnhandledExceptionInspection $property from parent::getProperties() */
				$property = new Reflection_Property($this->name, $reflection_property->name);
				$property->final_class       = $final_class;
				$properties[$property->name] = $property;
			}
		}
		if ($extends) {
			$parent_class = $this->getParentClass();
			while ($parent_class) {
				$parent_class_properties = $parent_class->getProperties(
					in_array(T_USE, $flags) ? [T_USE] : [], $final_class, Reflection_Property::IS_PRIVATE
				);
				$properties   = array_merge($parent_class_properties, $properties);
				$parent_class = $parent_class->getParentClass();
			}
		}
		// if no T_USE : remove properties from class traits
		// TODO This crashes, for the moment. Not used, but see what matters
		/*
		if ($properties && is_array($flags) && !in_array(T_USE, $flags)) {
			$trait_properties = [];
			foreach ($this->getTraits() as $trait) {
				$trait_properties = array_merge($trait_properties, $trait->getProperties([T_USE]));
			}
			if ($trait_properties) {
				$properties = array_diff_key($properties, $trait_properties);
			}
		}
		*/
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
	 * @param $name string The name of the property to get, or a property.path
	 * @return Reflection_Property
	 * @throws ReflectionException
	 */
	#[ReturnTypeWillChange]
	public function getProperty(string $name) : Reflection_Property
	{
		// property.path
		if (str_contains($name, DOT)) {
			return new Reflection_Property($this->name, $name);
		}
		// property_name
		$property = parent::getProperty($name);
		return new Reflection_Property($this->name, $property->name);
	}

	//------------------------------------------------------------------------------------- getTraits
	/**
	 * Gets traits
	 *
	 * @noinspection PhpDocMissingThrowsInspection from parent::getTraits()
	 * @return Reflection_Class[]
	 */
	public function getTraits() : array
	{
		$traits = [];
		foreach (parent::getTraits() as $trait) {
			/** @noinspection PhpUnhandledExceptionInspection from parent::getTraits() */
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
	public function isA(string $name, array $flags = []) : bool
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
	 * PHP ReflectionClass behaviour for this is :
	 * - Abstract classes are abstract (this is the main use)
	 * - Interfaces are not abstract
	 * - PHP before 7.0 : Traits are abstract. PHP 7.0 and next : Traits are not abstract.
	 *
	 * Reflection_Class behaviour will be :
	 * - Abstract classes, Interfaces and Traits are always abstract
	 *
	 * @return boolean
	 */
	public function isAbstract() : bool
	{
		return parent::isAbstract() || $this->isInterface() || $this->isTrait();
	}

	//--------------------------------------------------------------------------------------- isClass
	/**
	 * Checks if this class is a class (not an interface or a trait)
	 *
	 * @return boolean
	 */
	public function isClass() : bool
	{
		return !$this->isInterface() && !$this->isTrait();
	}

	//-------------------------------------------------------------------------------- sortProperties
	/**
	 * Sort the properties list from @display_order class annotation(s)
	 *
	 * @param $properties     Reflection_Property[] key is the name of the property
	 * @param $display_orders List_Annotation[]|string[][] additional display order annotations
	 * @return Reflection_Property[] key is the name of the property
	 */
	public function sortProperties(array $properties, array $display_orders = []) : array
	{
		/** @var $annotations Display_Order_Annotation[] */
		$annotations = array_merge(
			$display_orders,
			$this->getListAnnotations(Display_Order_Annotation::ANNOTATION)
		);
		if ($annotations) {
			$lists = [];
			foreach ($annotations as $annotation) {
				if (is_array($annotation) && $annotation) {
					$lists[] = $annotation;
				}
				elseif ($annotation->value) {
					$lists[] = $annotation->value;
				}
			}
			$sorted_properties = [];
			foreach ((new Value_Lists($lists))->assembly() as $property_name) {
				if (isset($properties[$property_name])) {
					$sorted_properties[$property_name] = $properties[$property_name];
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
