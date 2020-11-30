<?php
namespace ITRocks\Framework\PHP;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Reflection;
use ITRocks\Framework\Reflection\Annotation\Annoted;
use ITRocks\Framework\Reflection\Annotation\Parser;
use ITRocks\Framework\Reflection\Interfaces;
use ITRocks\Framework\Reflection\Interfaces\Has_Doc_Comment;
use ITRocks\Framework\Tools\Call_Stack;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Tools\Namespaces;

/**
 * A reflection class parser that uses php tokens to parse php source code instead of loading
 * the class. Useful to use reflection on a class before modifying it and finally load it for real.
 */
class Reflection_Class implements Has_Doc_Comment, Interfaces\Reflection_Class
{
	use Annoted;
	use Tokens_Parser;

	//--------------------------------------------------------------------------------- T_DOC_EXTENDS
	const T_DOC_EXTENDS = 'T_DOC_EXTENDS';

	//------------------------------------------------------------------------------------ $constants
	/**
	 * @var double[]|integer[]|string[] Constant name in key, constant value in value
	 */
	private $constants;

	//---------------------------------------------------------------------------------- $doc_comment
	/**
	 * @var string
	 */
	private $doc_comment;

	//----------------------------------------------------------------------------------- $interfaces
	/**
	 * @var Reflection_Class[]|string[]
	 */
	private $interfaces;

	//--------------------------------------------------------------------------- $interfaces_methods
	/**
	 * @var Reflection_Method[]
	 */
	private $interfaces_methods;

	//---------------------------------------------------------------------------------- $is_abstract
	/**
	 * @var boolean
	 */
	private $is_abstract;

	//------------------------------------------------------------------------------------- $is_final
	/**
	 * @var boolean
	 */
	private $is_final;

	//----------------------------------------------------------------------------------------- $line
	/**
	 * @var integer the line where the class declaration starts into source
	 */
	public $line;

	//-------------------------------------------------------------------------------------- $methods
	/**
	 * @var Reflection_Method[]
	 */
	private $methods;

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @var string The name of the class
	 */
	public $name;

	//--------------------------------------------------------------------------------------- $parent
	/**
	 * This parent is originally set as the parent class name, but is replaced by the replacement
	 * class name from Builder if there is one by getParent() and getParentName()
	 *
	 * @var Reflection_Class|string
	 */
	private $parent;

	//---------------------------------------------------------------------------- $parent_class_name
	/**
	 * This is the parent class name written into the source code
	 *
	 * @var string
	 */
	private $parent_class_name;

	//----------------------------------------------------------------------------- $parent_constants
	/**
	 * @var double[]|integer[]|string[]
	 */
	private $parent_constants;

	//------------------------------------------------------------------------------- $parent_methods
	/**
	 * @var Reflection_Method[]
	 */
	private $parent_methods;

	//---------------------------------------------------------------------------- $parent_properties
	/**
	 * @var Reflection_Property[]
	 */
	private $parent_properties;

	//----------------------------------------------------------------------------------- $properties
	/**
	 * @var Reflection_Property[]
	 */
	private $properties;

	//------------------------------------------------------------------------------------- $requires
	/**
	 * @var integer[] key is a string PHP file path, value is the line number where it is declared
	 */
	public $requires;

	//---------------------------------------------------------------------------- $short_trait_names
	/**
	 * @var string[] key is the full name of the class, value is the short name as in source code
	 */
	public $short_trait_names;

	//--------------------------------------------------------------------------------------- $source
	/**
	 * The PHP source reflection object containing the class
	 *
	 * @var Reflection_Source
	 */
	public $source;

	//----------------------------------------------------------------------------------------- $stop
	/**
	 * @var integer the line where the class declaration stops into source
	 */
	public $stop;

	//------------------------------------------------------------------------------ $trait_constants
	/**
	 * @var double[]|integer[]|string[]
	 */
	private $trait_constants;

	//--------------------------------------------------------------------------------------- $traits
	/**
	 * @var Reflection_Class[]|string[]
	 */
	private $traits;

	//------------------------------------------------------------------------------- $traits_methods
	/**
	 * @var Reflection_Method[]
	 */
	private $traits_methods;

	//---------------------------------------------------------------------------- $traits_properties
	/**
	 * @var Reflection_Property[]
	 */
	private $traits_properties;

	//----------------------------------------------------------------------------------------- $type
	/**
	 * @values T_CLASS, T_INTERFACE, T_TRAIT
	 * @var integer|null
	 */
	public $type = null;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Constructs a reflection class object using PHP source code
	 *
	 * @param $source Reflection_Source The PHP source code object that contains the class
	 * @param $name   string The name of the class.
	 *                If not set, the first class in source will be reflected.
	 */
	public function __construct(Reflection_Source $source, $name = null)
	{
		$this->source = $source;

		unset($this->line);
		unset($this->name);
		unset($this->stop);

		if (isset($name)) {
			$this->name = (substr($name, 0, 1) === BS) ? substr($name, 1) : $name;
		}
	}

	//----------------------------------------------------------------------------------------- __get
	/**
	 * @param $property_name string
	 * @return mixed
	 */
	public function __get($property_name)
	{
		if (in_array($property_name, ['line', 'name', 'type'])) {
			$this->scanUntilClassName();
		}
		elseif ($property_name === 'stop') {
			$this->scanUntilClassEnds();
		}
		return $this->$property_name;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string The name of the class
	 */
	public function __toString()
	{
		return $this->name;
	}

	//------------------------------------------------------------------------------------------ free
	/**
	 * Reset some data that will be re-calculated freeing them
	 *
	 * This can be called to be sure that if parent data changed, current data will change too.
	 * Set $all to true if you change anything into the source, to ensure that every source cache
	 * has been reset.
	 *
	 * @param $all boolean if true, reset the reflection class as if it is just loaded
	 */
	public function free($all = false)
	{
		if ($all) {
			$property_defaults = get_class_vars(get_class($this));
			unset($property_defaults['annotations_cache']);
			unset($property_defaults['name']);
			unset($property_defaults['source']);
			foreach ($property_defaults as $property_name => $default_value) {
				if (!isset(static::$$property_name)) {
					$this->$property_name = $default_value;
				}
			}
		}
		else {
			// parent may have been changed into a built class, more traits may have been added to them
			$this->parent_methods    = null;
			$this->parent_properties = null;
			// more traits may have been added
			$this->traits_methods    = null;
			$this->traits_properties = null;
		}
	}

	//----------------------------------------------------------------------------------- getConstant
	/**
	 * Gets defined constant value
	 *
	 * @param $name string
	 * @return mixed
	 */
	public function getConstant(string $name)
	{
		$constants = $this->getConstants([]);
		if (!isset($constants[$name])) {
			$constants = $this->getConstants([T_USE]);
			if (!isset($constants[$name])) {
				$constants = $this->getConstants([T_EXTENDS]);
			}
		}
		return array_key_exists($name, $constants) ? $constants[$name] : false;
	}

	//---------------------------------------------------------------------------------- getConstants
	/**
	 * Gets defined constants from a class
	 *
	 * @param $flags integer[] T_EXTENDS, T_USE
	 * @return mixed[] Constant name in key, constant value in value
	 */
	public function getConstants($flags = [T_EXTENDS, T_USE])
	{
		if (!$this->constants) {
			$this->scanUntilClassEnds();
		}
		$constants = $this->constants;

		if ($flags) {
			$flip = array_flip($flags);
			if (isset($flip[T_USE])) {
				if (!isset($this->trait_constants)) {
					$this->trait_constants = [];
					foreach ($this->getTraits() as $trait) {
						$this->trait_constants = array_merge(
							$trait->getConstants([T_USE]), $this->trait_constants
						);
					}
				}
				$constants = array_merge($this->trait_constants, $constants);
			}
			if (isset($flip[T_EXTENDS])) {
				if (!isset($this->parent_constants)) {
					$this->parent_constants = ($parent = $this->getParentClass())
						? $parent->getConstants([T_EXTENDS, T_USE])
						: [];
				}
				$constants = array_merge($this->parent_constants, $constants);
			}
		}

		return $constants;
	}

	//-------------------------------------------------------------------------------- getConstructor
	/**
	 * Gets the constructor of the reflected class
	 *
	 * @return Reflection_Method
	 */
	public function getConstructor()
	{
		$methods = $this->getMethods();
		if (isset($methods['__construct'])) {
			return $methods['__construct'];
		}
		else {
			$short_class_name = Namespaces::shortClassName($this->name);
			if (isset($methods[$short_class_name])) {
				return $methods[$short_class_name];
			}
		}
		return null;
	}

	//-------------------------------------------------------------------------- getDefaultProperties
	/**
	 * Gets default value of properties
	 *
	 * TODO not implemented yet
	 *
	 * @param $flags integer[] T_EXTENDS, T_USE
	 * @return array
	 */
	public function getDefaultProperties(array $flags = [])
	{
		return [];
	}

	//--------------------------------------------------------------------------------- getDocComment
	/**
	 * Accumulates documentations of parents and the class itself
	 *
	 * @param $flags   integer[] T_EXTENDS, T_IMPLEMENTS, T_USE
	 * @param $already boolean[] for internal use (recursion) : already got those classes (keys)
	 * @return string
	 */
	public function getDocComment(array $flags = null, array &$already = [])
	{
		if (!isset($flags)) {
			$flags = [T_EXTENDS, T_IMPLEMENTS, T_USE];
		}
		if (!isset($this->doc_comment)) {
			$this->scanUntilClassName();
		}
		$doc_comment = $this->doc_comment;

		if ($flags) {
			$flip = array_flip($flags);
			if (($this->type !== T_INTERFACE) && isset($flip[T_USE])) {
				foreach ($this->getTraits() as $trait) {
					if ($comment = $trait->getDocComment($flags, $already)) {
						$doc_comment .= LF . Parser::DOC_COMMENT_IN . $trait->name . LF . $comment;
					}
				}
			}
			if (($this->type !== T_TRAIT) && isset($flip[T_EXTENDS])) {
				if ($parent = $this->getParentClass()) {
					if ($comment = $parent->getDocComment($flags, $already)) {
						$doc_comment .= LF . Parser::DOC_COMMENT_IN . $parent->name . LF . $comment;
					}
				}
			}
			if (($this->type !== T_TRAIT) && isset($flip[T_IMPLEMENTS])) {
				foreach ($this->getInterfaces() as $interface) {
					if (!isset($already[$interface->name])) {
						$already[$interface->name] = true;
						if ($comment = $interface->getDocComment($flags)) {
							$doc_comment .= LF . Parser::DOC_COMMENT_IN . $interface->name . LF . $comment;
						}
					}
				}
			}
		}

		return $doc_comment;
	}

	//--------------------------------------------------------------------------------- getDocExtends
	/**
	 * Gets the classes that are into @extends instead of use to allow diamond multiple inheritance
	 *
	 * @return Reflection_Class[]
	 */
	public function getDocExtends()
	{
		$extends = [];
		$expr    = '%'
			. '\n\s+\*\s+'     // each line beginning by '* '
			. '@extends'       // extends annotation
			. '\s+([\\\\\w]+)' // 1 : class name
			. '%';
		if (preg_match_all($expr, $this->getDocComment([]), $matches)) {
			foreach ($matches[1] as $match) {
				$extends[] = Reflection_Class::of($this->fullClassName($match));
			}
		}
		return $extends;
	}

	//----------------------------------------------------------------------------------- getFileName
	/**
	 * Gets the filename of the file in which the class has been defined
	 *
	 * @return string
	 */
	public function getFileName()
	{
		return $this->source->file_name;
	}

	//----------------------------------------------------------------------------- getInterfaceNames
	/**
	 * @return string[]
	 */
	public function getInterfaceNames()
	{
		if (!isset($this->interfaces)) {
			$this->scanUntilClassBegins();
		}
		return array_keys($this->interfaces);
	}

	//--------------------------------------------------------------------------------- getInterfaces
	/**
	 * @return Reflection_Class[]
	 */
	public function getInterfaces()
	{
		if (!isset($this->interfaces)) {
			$this->scanUntilClassBegins();
		}
		foreach ($this->interfaces as $interface_name => $interface) {
			if (!is_object($interface)) {
				$interface = $this->source->getOutsideClass($interface_name);
				if (!$interface->source->isInternal()) {
					$this->interfaces[$interface_name] = $interface;
				}
				else {
					unset($this->interfaces[$interface_name]);
				}
			}
		}
		return $this->interfaces;
	}

	//------------------------------------------------------------------------------------ getMethods
	/**
	 * @param $flags integer[] T_EXTENDS, T_IMPLEMENTS, T_USE, self::T_DOCEXTENDS
	 * @return Reflection_Method[] key is the name of the method
	 */
	public function getMethods($flags = null)
	{
		if (!isset($this->methods)) {
			$this->scanUntilClassEnds();
		}
		$methods = $this->methods;

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
						$this->parent_methods = $parent->getMethods([T_EXTENDS, T_IMPLEMENTS, T_USE]);
					}
				}
				$methods = array_merge($this->parent_methods, $methods);
			}
			if (isset($flip[T_IMPLEMENTS])) {
				if (!isset($this->interfaces_methods)) {
					$this->interfaces_methods = [];
					foreach ($this->getInterfaces() as $interface) {
						$this->interfaces_methods = array_merge(
							$interface->getMethods([T_EXTENDS, T_IMPLEMENTS]), $this->interfaces_methods
						);
					}
				}
				$methods = array_merge($this->interfaces_methods, $methods);
			}
			if (isset($flip[self::T_DOC_EXTENDS])) {
				foreach ($this->getDocExtends() as $extends) {
					$methods = array_merge($extends->getMethods([self::T_DOC_EXTENDS, T_USE]), $methods);
				}
			}
		}

		return $methods;
	}

	//--------------------------------------------------------------------------------------- getName
	/**
	 * @return string
	 */
	public function getName()
	{
		if (!isset($this->name)) {
			$this->scanUntilClassName();
		}
		return $this->name;
	}

	//------------------------------------------------------------------------------ getNamespaceName
	/**
	 * Gets namespace name
	 *
	 * @return string
	 */
	public function getNamespaceName()
	{
		if (!isset($this->namespace)) {
			$this->scanUntilClassName();
		}
		return $this->namespace;
	}

	//------------------------------------------------------------------------------- getNamespaceUse
	/**
	 * @return string[]
	 */
	public function getNamespaceUse()
	{
		if (!isset($this->use)) {
			$this->scanUntilClassName();
		}
		return $this->use ? array_keys($this->use) : [];
	}

	//-------------------------------------------------------------------------------- getParentClass
	/**
	 * Gets parent Reflection_Class object
	 *
	 * If parent is an internal class, of if there is no parent : will return null.
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return Reflection_Class
	 */
	public function getParentClass()
	{
		if (!isset($this->parent)) {
			$this->scanUntilClassBegins();
		}
		if ($this->parent) {
			$this->parentReplacement();
		}
		if (is_string($this->parent)) {
			$parent = $this->source->getOutsideClass($this->parent);
			if ($parent->source->isInternal()) {
				if (!class_exists($parent->name, false)) {
					foreach (array_keys($this->source->requires) as $require) {
						/** @noinspection PhpIncludeInspection is dynamic */
						/** @noinspection PhpUsageOfSilenceOperatorInspection Some requires have @ */
						@include_once $require;
					}
				}
				/** @noinspection PhpUnhandledExceptionInspection parent class is always valid */
				$this->parent = new Reflection\Reflection_Class($parent->name);
			}
			else {
				$this->parent = $parent;
			}
		}
		return $this->parent ?: null;
	}

	//--------------------------------------------------------------------------------- getParentName
	/**
	 * @return string
	 */
	public function getParentName()
	{
		if (!isset($this->parent)) {
			$this->scanUntilClassBegins();
			if (!isset($this->parent)) {
				$this->parent = false;
			}
		}
		if ($this->parent) {
			$this->parentReplacement();
		}
		return $this->parent ? (is_string($this->parent) ? $this->parent : $this->parent->name) : null;
	}

	//-------------------------------------------------------------------- getParentOriginalClassName
	/**
	 * @return string
	 */
	public function getParentOriginalClassName()
	{
		if (!isset($this->parent)) {
			$this->getParentName();
		}
		return $this->parent_class_name;
	}

	//--------------------------------------------------------------------------------- getProperties
	/**
	 * A little difference with PHP's Reflection\Reflection_Class::getProperties : if T_USE flag is
	 * not set, this will not return properties that are declared into traits.
	 *
	 * @param $flags       integer[]|string[] Restriction. self::T_SORT has no effect (never applied).
	 *                     flags @default [T_EXTENDS, T_USE] @values T_EXTENDS, T_USE
	 * @param $final_class Reflection_Class force the final class to this name (mostly for internal use)
	 * @return Reflection_Property[] key is the name of the property
	 */
	public function getProperties($flags = null, $final_class = null)
	{
		if (!isset($flags)) {
			$flags = [T_EXTENDS, T_USE];
		}
		if (!isset($this->properties)) {
			$this->scanUntilClassEnds();
		}
		$properties = $this->properties;

		if (isset($final_class)) {
			foreach ($this->properties as $property) {
				$property->final_class = $final_class;
			}
		}

		if ($flags) {
			$flip = array_flip($flags);
			if (isset($flip[T_USE])) {
				if (!isset($this->traits_properties)) {
					$this->traits_properties = [];
					foreach ($this->getTraits() as $trait) {
						$this->traits_properties = array_merge(
							$trait->getProperties([T_USE], $final_class), $this->traits_properties
						);
					}
				}
				$properties = array_merge($this->traits_properties, $properties);
			}
			if (isset($flip[T_EXTENDS])) {
				if (!isset($this->parent_properties)) {
					$this->parent_properties = ($parent = $this->getParentClass())
						? $parent->getProperties([T_EXTENDS, T_USE], $final_class)
						: [];
				}
				$properties = array_merge($this->parent_properties, $properties);
			}
		}

		return $properties;
	}

	//----------------------------------------------------------------------------------- getProperty
	/**
	 * Retrieves reflected properties
	 *
	 * Properties visible for current class or private from parents can be retrieved.
	 * If there is conflicts on a private property name existing in child and parent, the highest
	 * level property (from child class) will be returned
	 *
	 * @param $name string The name of the property to get
	 * @return Reflection_Property
	 */
	public function getProperty($name)
	{
		return $this->getProperties()[$name];
	}

	//------------------------------------------------------------------------------- getSetClassName
	/**
	 * @return string
	 */
	public function getSetClassName()
	{
		$expr = '%'
			. '\n\s+\*\s+'     // each line beginning by '* '
			. '@set'           // set annotation
			. '\s+([\\\\\w]+)' // 1 : class name
			. '%';
		preg_match($expr, $this->getDocComment([]), $match);
		return $match
			? Namespaces::defaultFullClassName($match[1], $this->name)
			: Names::singleToSet($this->name);
	}

	//---------------------------------------------------------------------------------- getShortName
	/**
	 * @retun string
	 */
	public function getShortName()
	{
		if (!isset($this->name)) {
			$this->scanUntilClassName();
		}
		return (($pos = strrpos($this->name, BS)) !== false)
			? substr($this->name, $pos + 1)
			: $this->name;
	}

	//---------------------------------------------------------------------------------- getStartLine
	/**
	 * Gets starting line number
	 *
	 * @return integer
	 */
	public function getStartLine()
	{
		if (!isset($this->line)) {
			$this->scanUntilClassName();
		}
		return $this->line;
	}

	//----------------------------------------------------------------------------------- getStopLine
	/**
	 * @return integer
	 */
	public function getStopLine()
	{
		if (!isset($this->stop)) {
			$this->scanUntilClassEnds();
		}
		return $this->stop;
	}

	//------------------------------------------------------------------------------------- getTokens
	/**
	 * @return array
	 */
	public function & getTokens()
	{
		if (!isset($this->tokens)) {
			$this->tokens =& $this->source->getTokens();
		}
		return $this->tokens;
	}

	//--------------------------------------------------------------------------------- getTraitNames
	/**
	 * @return string[]
	 */
	public function getTraitNames()
	{
		if (!isset($this->traits)) {
			$this->scanUntilClassEnds();
		}
		return array_keys($this->traits);
	}

	//------------------------------------------------------------------------------------- getTraits
	/**
	 * @return Reflection_Class[]
	 */
	public function getTraits()
	{
		if (!isset($this->traits)) {
			$this->scanUntilClassEnds();
		}
		foreach ($this->traits as $trait_name => $trait) {
			if (!is_object($trait)) {
				$trait = $this->source->getOutsideClass($trait_name);
				if (!$trait->source->isInternal()) {
					$this->traits[$trait_name] = $trait;
				}
				else {
					unset($this->traits[$trait_name]);
				}
			}
		}
		return $this->traits;
	}

	//--------------------------------------------------------------------------------------- getType
	/**
	 * @return integer
	 */
	public function getType()
	{
		if (!$this->type) {
			$this->scanUntilClassName();
		}
		return $this->type;
	}

	//------------------------------------------------------------------------------ implementsMethod
	/**
	 * Returns true if this class or any of its direct traits implements the method.
	 * Direct traits are all the traits of the class, not the traits of the parent classes.
	 *
	 * @param $method_name    string
	 * @param $include_traits boolean if false, look in class only
	 * @return boolean
	 */
	public function implementsMethod($method_name, $include_traits = true)
	{
		$methods = $this->getMethods($include_traits ? [T_USE] : []);
		return isset($methods[$method_name]) && !$methods[$method_name]->isAbstract();
	}

	//---------------------------------------------------------------------------- implementsProperty
	/**
	 * Returns true if this class or any of its direct traits declares/implements the property.
	 * Direct traits are all the traits of the class, not the traits of the parent classes.
	 *
	 * @param $property_name  string
	 * @param $include_traits boolean if false, look in class only
	 * @return boolean
	 */
	public function implementsProperty($property_name, $include_traits = true)
	{
		$properties = $this->getProperties($include_traits ? [T_USE] : []);
		return isset($properties[$property_name]);
	}

	//----------------------------------------------------------------------------------- inNamespace
	/**
	 * Checks if in namespace
	 *
	 * @return boolean
	 */
	public function inNamespace()
	{
		if (!isset($this->namespace)) {
			$this->scanUntilClassName();
		}
		return $this->namespace ? true : false;
	}

	//------------------------------------------------------------------------------------------- isA
	/**
	 * Returns true if the class has $name into its parents, interfaces or traits
	 *
	 * @param $name string
	 * @param $flags integer[] T_EXTENDS, T_IMPLEMENTS, T_USE
	 * @return boolean
	 * @todo works only with parents : add interfaces and traits
	 */
	public function isA($name, array $flags = [])
	{
		if ($name == $this->name) {
			return true;
		}
		$parent_name = $this->getParentName();
		return (($parent_name == $name) || ($parent_name && $this->getParentClass()->isA($name)));
	}

	//------------------------------------------------------------------------------------ isAbstract
	/**
	 * Checks if class is abstract
	 *
	 * Abstracts are :
	 * - abstract class
	 * - interface
	 * - trait
	 *
	 * @return boolean
	 */
	public function isAbstract()
	{
		if (!isset($this->is_abstract)) {
			$this->scanUntilClassName();
		}
		return $this->is_abstract;
	}

	//--------------------------------------------------------------------------------------- isClass
	/**
	 * @return boolean
	 */
	public function isClass()
	{
		return $this->type === T_CLASS;
	}

	//--------------------------------------------------------------------------------------- isFinal
	/**
	 * Checks if class is final
	 *
	 * @return boolean
	 */
	public function isFinal()
	{
		if (!isset($this->is_final)) {
			$this->scanUntilClassName();
		}
		return $this->is_final;
	}

	//------------------------------------------------------------------------------------ isInstance
	/**
	 * Checks class for instance
	 *
	 * @param $object object
	 * @return boolean
	 */
	public function isInstance(object $object)
	{
		return is_a($object, $this->name, true);
	}

	//----------------------------------------------------------------------------------- isInterface
	/**
	 * Checks if the class is an interface
	 *
	 * @return boolean
	 */
	public function isInterface()
	{
		return $this->type === T_INTERFACE;
	}

	//------------------------------------------------------------------------------------ isInternal
	/**
	 * Checks if class is defined internally by an extension, or the core
	 *
	 * @return boolean
	 */
	public function isInternal()
	{
		return false;
	}

	//--------------------------------------------------------------------------------------- isTrait
	/**
	 * @return boolean
	 */
	public function isTrait()
	{
		return $this->type === T_TRAIT;
	}

	//--------------------------------------------------------------------------------- isUserDefined
	/**
	 * Checks if user defined
	 *
	 * @return boolean
	 */
	public function isUserDefined()
	{
		return true;
	}

	//-------------------------------------------------------------------------------------------- of
	/**
	 * @param $class_name string
	 * @return Reflection_Class
	 */
	public static function of($class_name)
	{
		return Reflection_Source::ofClass($class_name)->getClass($class_name);
	}

	//----------------------------------------------------------------------------- parentReplacement
	/**
	 * Replaces the parent by its replacement class, if already built
	 */
	protected function parentReplacement()
	{
		$parent_class_name = is_string($this->parent) ? $this->parent : $this->parent->name;
		if (Builder::current()->isReplaced($parent_class_name, true)) {
			$replacement_parent_class_name = Builder::className($parent_class_name);
			if (
				($replacement_parent_class_name !== $parent_class_name)
				&& ($replacement_parent_class_name !== $this->name)
			) {
				// Builder will not refer to an internal class : can build it directly
				$replacement_parent_class = $this->source->getOutsideClass($replacement_parent_class_name);
				// this parent class is valid only if it has not the current class as parent class
				$parent_parent_class = $replacement_parent_class;
				while ($parent_parent_class = $parent_parent_class->getParentClass()) {
					if ($parent_parent_class->name === $this->name) {
						$replacement_parent_class      = $this->parent;
						$replacement_parent_class_name = $parent_class_name;
						break;
					}
				}
				if ($replacement_parent_class_name !== $parent_class_name) {
					$this->parent = $replacement_parent_class;
				}
			}
		}
	}

	//----------------------------------------------------------------------------------------- regex
	/**
	 * Gets the preg expression to find the class definition in a PHP source that contains one class
	 *
	 * Preg matching records will be :
	 * - 1 : the namespace
	 * - 2 : indent characters (including '\n')
	 * - 3 : the last phpdocumentor documentation before the function keyword
	 * - 4 : the full class declaration prototype string
	 * - 5 : 'final' or empty
	 * - 6 : 'abstract' or empty
	 * - 7 : 'class', 'interface' or 'trait'
	 * - 8 : the name of the class
	 * - 9 : the 'extends Parent_Name ' string
	 * - 10 : the 'implements Interface1, Interface2 ' string
	 *
	 * @return string
	 */
	public static function regex()
	{
		return '%'
		. 'namespace\s+([\\\\\w]+)\s*?[\{\;]'       // 1 : namespace
		. '(?:.*\n)*?'                              // next lines
		. '(\n\s*?)'                                // 2 : indent
		. '(?:(/\*\*\n(?:\s*\*.*\n)*\s*\*/)\n\s*)?' // 3 : documentation
		. '(?:\/\*.*\*/\n\s*)?'                     // ignored one-line documentation
		. '('                                       // 4 : prototype
		. '(?:(final)\s+)?'                         // 5 : final
		. '(?:(abstract)\s+)?'                      // 6 : abstract
		. '(class|interface|trait)\s+'              // 7 : type = class, interface or trait
		. '(\w+)\s*'                                // 8 : name
		. '(?:extends\s+([\\\\\w]+)\s*)?'           // 9 : extends
		. '(?:implements\s+((?:.*?\n)*?)\s*)?'      // 10 : implements
		. '\{'                                      // start class code
		. ')'
		. '%';
	}

	//-------------------------------------------------------------------------- scanUntilClassBegins
	/**
	 * Scan tokens until the class begins
	 */
	private function scanUntilClassBegins()
	{
		if (!isset($this->interfaces)) {
			$this->scanUntilClassName();

			$this->interfaces = [];
			$this->parent     = null;
			$this->requires   = [];

			$this->getTokens();
			if (!$this->tokens) return;
			$token = $this->tokens[$this->token_key];
			while ($token !== '{') {
				if (is_array($token) && in_array($token[0], [T_EXTENDS, T_IMPLEMENTS])) {
					foreach ($this->scanClassNames() as $class_name => $line) {
						$class_name = $this->fullClassName($class_name);
						if ($token[0] === T_IMPLEMENTS) {
							$this->interfaces[$class_name] = $class_name;
						}
						else {
							$this->parent_class_name = $class_name;
							$this->parent            = $class_name;
						}
					}
					$token = $this->tokens[$this->token_key];
				}
				else {
					$token = $this->tokens[++$this->token_key];
				}
			}

			if ($this->parent) {
				$this->parentReplacement();
			}

		}
	}

	//---------------------------------------------------------------------------- scanUntilClassEnds
	/**
	 * Scan tokens until the class ends
	 */
	private function scanUntilClassEnds()
	{
		if (!isset($this->methods)) {
			$this->scanUntilClassBegins();

			$this->constants         = [];
			$this->methods           = [];
			$this->properties        = [];
			$this->short_trait_names = [];
			$this->traits            = [];
			unset($this->stop);

			$depth = 0;
			$visibility_token = null;

			$this->getTokens();
			if (!$this->tokens) return;
			$token = $this->tokens[$this->token_key];
			do {

				switch ($token[0]) {

					case T_USE:
						if ($depth === 1) {
							foreach ($this->scanTraitNames() as $short_trait_name => $line) {
								$trait_name                           = $this->fullClassName($short_trait_name);
								$this->short_trait_names[$trait_name] = $short_trait_name;
								$this->traits[$trait_name]            = $trait_name;
							}
						}
						break;

					case T_CONST:
						$const_name = $equal = null;
						while (($token = $this->tokens[++$this->token_key]) !== ';') {
							if (is_array($token)) {
								if ($token[0] === T_STRING) {
									$const_name = $token[1];
								}
								elseif ($const_name && $equal) {
									$const_value = $token[1];
									if ($token[0] === T_CONSTANT_ENCAPSED_STRING) {
										if (($const_value[0] === Q) && ($const_value[strlen($const_value) - 1] === Q)) {
											$const_value = substr($const_value, 1, -1);
										}
										$this->constants[$const_name] = $const_value;
									}
									elseif ($token[0] === T_DNUMBER) {
										$this->constants[$const_name] = (double)$const_value;
									}
									elseif ($token[0] === T_LNUMBER) {
										$this->constants[$const_name] = (integer)$const_value;
									}
								}
							}
							elseif ($token === '=') {
								$equal = true;
							}
						}
						break;

					case T_PRIVATE:
					case T_PROTECTED:
					case T_PUBLIC:
					case T_VAR:
						if ($depth === 1) {
							$visibility_token = $this->token_key;
						}
						break;

					case T_VARIABLE:
						if (($depth === 1) && isset($visibility_token)) {
							$property_name = substr($token[1], 1);
							$visibility = $this->tokens[$visibility_token][0];
							$property = new Reflection_Property(
								$this,
								$property_name,
								$this->tokens[$visibility_token][2],
								$visibility_token,
								($visibility === T_VAR) ? T_PUBLIC : $visibility
							);
							$this->properties[$property_name] = $property;
						}
						$visibility_token = null;
						break;

					case T_FUNCTION:
						if ($depth === 1) {
							$line = $token[2];
							$token_key = $this->token_key;
							/** @noinspection PhpStatementHasEmptyBodyInspection ++ inside */
							while ($this->tokens[++$this->token_key][0] !== T_STRING);
							$token = $this->tokens[$this->token_key];
							$this->methods[$token[1]] = new Reflection_Method(
								$this, $token[1], $line, $token_key, $visibility_token ?: T_PUBLIC
							);
							$visibility_token = null;
						}
						break;

					case T_CURLY_OPEN:
					case T_DOLLAR_OPEN_CURLY_BRACES:
					case T_STRING_VARNAME:
					case '{':
						$depth ++;
						$visibility_token = null;
						break;

					case '}':
						$depth --;
						if (!$depth) {
							/** @noinspection PhpStatementHasEmptyBodyInspection -- inside */
							while (!is_array($token = $this->tokens[--$this->token_key]));
							$this->stop = $token[2];
							if ($token[0] === T_WHITESPACE) {
								$this->stop += substr_count($token[1], LF);
							}
						}
						$visibility_token = null;
						break;

				}

				if (!isset($this->stop)) {
					$token = $this->tokens[++$this->token_key];
				}

			} while (!isset($this->stop));

		}
	}

	//---------------------------------------------------------------------------- scanUntilClassName
	/**
	 * Scan tokens until class name
	 * This resets the tokens scan to start from the namespace declaration
	 */
	private function scanUntilClassName()
	{
		if (!isset($this->use)) {
			$this->getTokens();
			if (!$this->tokens) return;
			$token = $this->tokens[$this->token_key = 0];

			$this->namespace = '';
			$this->use       = [];
			do {

				$this->doc_comment = '';
				$this->is_abstract = false;
				$this->is_final    = false;

				while (!is_array($token) || !in_array($token[0], [T_CLASS, T_INTERFACE, T_TRAIT])) {
					if (is_array($token)) {
						switch ($token[0]) {

							case T_NAMESPACE:
								$this->namespace = $this->scanClassName();
								$this->use = [];
								break;

							case T_USE:
								foreach ($this->scanClassNames() as $used => $line) {
									$this->use[$used] = $used;
								}
								break;

							case T_DOC_COMMENT:
								$this->doc_comment .= $token[1];
								break;

							case T_ABSTRACT:
								$this->is_abstract = true;
								break;

							case T_FINAL:
								$this->is_final = true;
								break;

							case T_COMMENT: case T_WHITESPACE:
								break;

							default:
								$this->doc_comment = '';

						}
					}
					else {
						$this->doc_comment = '';
					}
					$token = $this->tokens[++$this->token_key];
				}

				$this->line = $token[2];
				$this->type = $token[0];
				if($this->type !== T_CLASS) {
					$this->is_abstract = true;
				}

				$class_name = $this->fullClassName($this->scanClassName(), false);

				if (
					($class_name !== $this->name) && (strtolower($class_name) === strtolower($this->name))
				) {
					$this->wrongCaseError($class_name, $this->name);
				}

			} while (!isset($this->name) || ($class_name !== $this->name));
			$this->name = $class_name;

		}
	}

	//-------------------------------------------------------------------------------- wrongCaseError
	/**
	 * @param $class_name       string
	 * @param $wrong_class_name string
	 */
	private function wrongCaseError($class_name, $wrong_class_name)
	{
		// look for the source class where the class name is wrong
		foreach ((new Call_Stack())->lines() as $line) {
			if (
				($line->object instanceof Interfaces\Reflection_Class)
				&& ($line->object->getName() !== $wrong_class_name)
			) {
				$into_class_name = $line->object->getName();
				break;
			}
		}
		// case mismatch is an error
		trigger_error(
			"Wrong case $wrong_class_name : you should replace with $class_name"
			. (isset($into_class_name) ? " into $into_class_name" : ''),
			E_USER_ERROR
		);
	}

}
