<?php
namespace ITRocks\Framework;

use ITRocks\Framework\Builder\Class_Builder;
use ITRocks\Framework\Mapper\Component;
use ITRocks\Framework\Mapper\Getter;
use ITRocks\Framework\PHP\Compiler;
use ITRocks\Framework\Plugin\Activable;
use ITRocks\Framework\Reflection\Annotation\Property\Link_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Store_Annotation;
use ITRocks\Framework\Reflection\Link_Class;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Tools\Current_With_Default;
use ReflectionClass;
use ReflectionException;

/**
 * The Builder plugin replaces 'new Class_Name' calls by 'Builder::create('Class_Name')' in order to
 * enable objects substitution
 *
 * You should always use these calls for ITRocks business objects instantiations.
 *
 * @todo remove dependencies
 */
class Builder implements Activable
{
	use Current_With_Default { current as private dCurrent; }

	//---------------------------------------------------------------------------------------- $build
	/**
	 * When true, class names replacement by built class names are accepted.
	 * Can be set temporarily to false when you don't want built class names.
	 *
	 * @var boolean
	 */
	public bool $build = true;

	//--------------------------------------------------------------------------------- $compositions
	/**
	 * Backup of the replacement compositions for built composed classes
	 * Once a class replaced by a string[] of interfaces and traits is compiled, its replacement
	 * structure is stored into compositions for hot recompiling on demand.
	 *
	 * @var array[]
	 */
	private array $compositions = [];

	//-------------------------------------------------------------------------------------- $enabled
	/**
	 * Set this to false in order to force disabling of class names replacements features
	 *
	 * @var boolean
	 */
	public bool $enabled = true;

	//--------------------------------------------------------------------------------- $replacements
	/**
	 * The key is an original class name to replace by a replacement class
	 * If the value is a string, this is the replacement class name
	 * If the value is a string[], this is the list of interfaces and traits to use to build a
	 * replacement class.
	 *
	 * The first time it is used, the replacement class is built and the value is replaced by the
	 * built class name.
	 *
	 * @var array[]|string[]
	 */
	private array $replacements = [];

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $replacements string[]|array[]|null key is parent class name associated to replacement
	 *   class values can be a class name or a string[] of interfaces and traits to add to the class
	 */
	public function __construct(array $replacements = null)
	{
		if (isset($replacements)) {
			$this->replacements = $replacements;
		}
	}

	//----------------------------------------------------------------------------------- __serialize
	/**
	 * @return array the array representation of the object or null
	 */
	public function __serialize() : array
	{
		return [$this->compositions, $this->replacements];
	}

	//--------------------------------------------------------------------------------- __unserialize
	/**
	 * @param $serialized array
	 */
	public function __unserialize(array $serialized)
	{
		[$this->compositions, $this->replacements] = $serialized;
	}

	//-------------------------------------------------------------------------------------- activate
	public function activate()
	{
		self::current($this);
	}

	//------------------------------------------------------------------------------------- className
	/**
	 * @param $class_name class-string<T>
	 * @return class-string<T>
	 * @template T
	 */
	public static function className(string $class_name) : string
	{
		return self::current()->replacementClassName($class_name);
	}

	//---------------------------------------------------------------------------------------- create
	/**
	 * @param $class_name class-string<T>
	 * @param $arguments  array some arguments into an array
	 * @return T
	 * @template T
	 * @throws ReflectionException
	 */
	public static function create(string $class_name, array $arguments = []) : object
	{
		return $arguments
			? self::current()->newInstanceArgs($class_name, $arguments)
			: self::current()->newInstance($class_name);
	}

	//----------------------------------------------------------------------------------- createClone
	/**
	 * Create a clone of the object, using a built class if needed
	 *
	 * @param $object            object
	 * @param $class_name        ?class-string<T> the new object will use the matching built class
	 *                           this class name must inherit from the object's class
	 * @param $properties_values array some properties value for the cloned object
	 * @param $same_identifier   boolean
	 * @return T
	 * @template T
	 * @throws ReflectionException
	 */
	public static function createClone(
		object $object, string $class_name = null, array $properties_values = [],
		bool $same_identifier = true
	) : object
	{
		$class_name        = self::className($class_name);
		$source_class_name = get_class($object);
		if (!isset($class_name)) {
			$class_name = self::className($source_class_name);
		}
		if ($class_name !== $source_class_name) {
			// initialises cloned object
			$clone = self::create($class_name);
			$destination_class = new Link_Class($class_name);
			// deactivate AOP
			if (isset($clone->_)) {
				$save_aop = $clone->_;
				unset($clone->_);
			}
			// copy official properties values from the source object
			$properties = (new Reflection_Class($source_class_name))->getProperties();
			foreach ($properties as $property) {
				if (!isset($save_aop[$property->name])) {
					$property->setValue($clone, $property->getValue($object));
				}
			}
			// copy unofficial properties values from the source object (ie AOP properties aliases)
			// clone collection objects using the destination collection property type
			$clone_collection = [];
			foreach (get_object_vars($object) as $property_name => $value) {
				if (($property_name !== '_') && !isset($properties[$property_name])) {
					$clone->$property_name = $value;
					if (isset($properties[rtrim($property_name, '_')])) {
						$property = $properties[rtrim($property_name, '_')];
						if (Link_Annotation::of($property)->isCollection()) {
							$element_class_from = $property->getType()->getElementTypeAsString();
							$property           = $destination_class->getProperty($property->name);
							$element_class_to   = $property->getType()->getElementTypeAsString();
							if ($element_class_to !== $element_class_from) {
								$clone_collection[substr($property_name, 0, -1)] = $element_class_to;
							}
						}
					}
				}
			}
			// reactivate AOP
			if (isset($save_aop)) {
				$clone->_ = $save_aop;
			}
			foreach ($clone_collection as $property_name => $element_class_to) {
				$elements = [];
				foreach ($object->$property_name as $key => $element) {
					$elements[$key] = Builder::createClone(
						$element, $element_class_to, [], $same_identifier
					);
				}
				$clone->$property_name = $elements;
			}
			// linked class object to link class object : store source object to linked object
			$destination_class = new Link_Class($class_name);
			if ($linked_class_name = $destination_class->getLinkedClassName()) {
				if ($linked_class_name === $source_class_name) {
					$destination_class->getLinkProperty()->setValue($clone, $object);
				}
			}
		}
		else {
			$clone = clone $object;
		}
		// identify destination object = source object, or disconnect destination object
		if ($same_identifier && Dao::getObjectIdentifier($object)) {
			Dao::replace($clone, $object, false);
		}
		else {
			Dao::disconnect($clone);
		}
		// copy added properties values to the cloned object
		if ($properties_values) {
			$properties = (new Reflection_Class($class_name))->getProperties();
			foreach ($properties_values as $property_name => $value) {
				$properties[$property_name]->setValue($clone, $value);
			}
		}
		return $clone;
	}

	//--------------------------------------------------------------------------------------- current
	/**
	 * @param $set_current Builder|null
	 * @return static
	 */
	public static function current(Builder $set_current = null) : static
	{
		return self::dCurrent($set_current);
	}

	//------------------------------------------------------------------------------------- fromArray
	/**
	 * Changes an array into an object
	 *
	 * You should set only public and non-static properties values
	 *
	 * @param $class_name            class-string<T>
	 * @param $array                 array
	 * @param $constructor_arguments array
	 * @return T
	 * @throws ReflectionException
	 * @template T
	 */
	public static function fromArray(
		string $class_name, array $array, array $constructor_arguments = []
	) : object
	{
		$object = self::create($class_name, $constructor_arguments);
		foreach ($array as $property_name => $value) {
			if ($property_name === '_') {
				continue;
			}
			elseif (str_ends_with($property_name, '_')) {
				$property_name = substr($property_name, 0, -1);
			}
			if (is_array($value)) {
				$property = new Reflection_Property($class_name, $property_name);
				$type     = $property->getType();
				if ($type->isClass()) {
					if ($type->isAbstractClass()) {
						$value = self::fromSubArray($value);
					}
					else {
						$property_class_name = $type->getElementTypeAsString();
						if ($type->isMultiple()) {
							$is_component = isA($property_class_name, Component::class);
							foreach ($value as $key => $val) {
								$element = self::fromArray($property_class_name, $val);
								if ($is_component) {
									/** @var $element Component */
									$element->setComposite($object);
								}
								$value[$key] = $element;
							}
						}
						else {
							$value = self::fromArray($property_class_name, $value);
						}
					}
				}
				$property->setValue($object, $value);
			}
			else {
				$object->$property_name = $value;
			}
		}
		return $object;
	}

	//---------------------------------------------------------------------------------- fromSubArray
	/**
	 * @param $array array
	 * @return array|object
	 */
	public static function fromSubArray(array $array) : array|object
	{
		if (isset($array[Store_Annotation::JSON_CLASS])) {
			return Getter::schemaDecode($array);
		}
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$array[$key] = self::fromSubArray($value);
			}
		}
		return $array;
	}

	//-------------------------------------------------------------------------------- getComposition
	/**
	 * Gets original replacement composition of the class name
	 *
	 * @param $class_name string
	 * @return string|string[]
	 */
	public function getComposition(string $class_name) : array|string
	{
		return $this->compositions[$class_name] ?? ($this->replacements[$class_name] ?? $class_name);
	}

	//------------------------------------------------------------------------------- getCompositions
	/**
	 * Gets all original replacements compositions
	 *
	 * @return array[]|string[]
	 */
	public function getCompositions() : array
	{
		return array_merge($this->replacements, $this->compositions);
	}

	//------------------------------------------------------------------------------------ isReplaced
	/**
	 * @param $class_name string
	 * @param $strict     boolean if true, the replacement class must be already built
	 * @return boolean
	 */
	public function isReplaced(string $class_name, bool $strict = false) : bool
	{
		if (!isset($this->replacements[$class_name])) {
			return false;
		}
		if (!$strict) {
			return true;
		}
		$replacement = $this->replacements[$class_name];
		while (is_string($replacement) && isset($this->replacements[$replacement])) {
			$replacement = $this->replacements[$replacement];
		}
		return is_string($replacement);
	}

	//--------------------------------------------------------------------------------- isReplacement
	/**
	 * Returns true if the class name is a replacement class
	 *
	 * @param $class_name string
	 * @return boolean
	 */
	public function isReplacement(string $class_name) : bool
	{
		return in_array($class_name, $this->replacements, true);
	}

	//----------------------------------------------------------------------------------- newInstance
	/**
	 * Return a new instance of given $class_name, using replacement class if exist
	 *
	 * @param $class_name class-string<T> may be short or full class name
	 * @return T
	 * @template T
	 */
	public function newInstance(string $class_name) : object
	{
		$class_name = $this->replacementClassName($class_name);
		return new $class_name();
	}

	//------------------------------------------------------------------------------- newInstanceArgs
	/**
	 * Return a new instance of given $class_name, using replacement class if exist
	 *
	 * @param $class_name class-string<T> may be short or full class name
	 * @param $args       array
	 * @return T
	 * @template T
	 * @throws ReflectionException
	 */
	public function newInstanceArgs(string $class_name, array $args) : object
	{
		$class_name = $this->replacementClassName($class_name);
		return (new ReflectionClass($class_name))->newInstanceArgs($args);
	}

	//-------------------------------------------------------------------------- replacementClassName
	/**
	 * Gets replacement class name for a parent class name or a list of traits to implement
	 *
	 * @param $class_name class-string<T> can be short or full class name
	 * @return class-string<T>
	 * @template T
	 */
	private function replacementClassName(string $class_name) : string
	{
		if ($this->enabled) {
			$result = $this->replacements[$class_name] ?? $class_name;
			if (is_array($result)) {
				if ($this->build) {
					$this->compositions[$class_name] = $result;
					$built_class_name = Class_Builder::builtClassName($class_name);
					$file_name        = Compiler::classToCacheFilePath($built_class_name);
					if (file_exists($file_name)) {
						$result = $built_class_name;
					}
					else {
						$result = (new Class_Builder)->build($class_name, $result);
					}
					$this->replacements[$class_name] = $result;
				}
				else {
					$result = $class_name;
				}
			}
			elseif (!$this->build && Class_Builder::isBuilt($result)) {
				$result = $class_name;
			}
		}
		else {
			$result = $class_name;
		}
		return (($class_name !== $result) && !Class_Builder::isBuilt($result))
			? $this->replacementClassName($result)
			: $result;
	}

	//-------------------------------------------------------------------------------- setReplacement
	/**
	 * Sets a new replacement
	 *
	 * Returns the hole replacement class name as you can set it back at will
	 *
	 * @param $class_name             class-string<T>
	 * @param $replacement_class_name class-string<T>|class-string<T>[]|null if null, the replacement
	 *        class is removed. string value for a replacement class, string[] for a list of
	 *        interfaces and traits.
	 * @return ?class-string<T> old replacement class name
	 * @template T
	 */
	public function setReplacement(string $class_name, array|string|null $replacement_class_name)
	: ?string
	{
		$result = $this->replacements[$class_name] ?? null;
		if (!isset($replacement_class_name)) {
			unset($this->compositions[$class_name]);
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
	 * @param $class_name class-string<T>|null
	 * @param $built      boolean if true, $class_name can be a built class : it will go to parent
	 * @return class-string<T>
	 * @template T
	 * @todo LOW should never be called with null, but it happens
	 */
	public function sourceClassName(string $class_name = null, bool $built = false) : string
	{
		while ($built && Class_Builder::isBuilt($class_name)) {
			$class_name = get_parent_class($class_name);
		}
		$found = array_search($class_name, $this->replacements);
		return ($found === false) ? $class_name : $this->sourceClassName($found);
	}

}
