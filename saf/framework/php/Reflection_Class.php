<?php
namespace SAF\Framework\PHP;

use SAF\Framework\Tools\Namespaces;
use SAF\Framework\Tools\Set;

/**
 * A reflection class parser that uses php tokens to parse php source code instead of loading
 * the class. Useful to use reflection on a class before modifying it and finally load it for real.
 */
class Reflection_Class
{
	use Tokens_Parser;

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

	//------------------------------------------------------------------------------------- $abstract
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
	 * @var Reflection_Class|string
	 */
	private $parent;

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

	//----------------------------------------------------------------------------------------- $type
	/**
	 * @values T_CLASS, T_INTERFACE, T_TRAIT
	 * @var integer
	 */
	public $type;

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
		unset($this->type);

		if (isset($name)) {
			$this->name = $name;
		}
	}

	//----------------------------------------------------------------------------------------- __get
	/**
	 * @param $property_name string
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

	//--------------------------------------------------------------------------------- getDocComment
	/**
	 * Cumulates documentations of parents and the class itself
	 *
	 * @param $flags integer[] T_EXTENDS, T_IMPLEMENTS, T_USE
	 * @return string
	 */
	public function getDocComment($flags = [])
	{
		if (!isset($this->doc_comment)) {
			$this->scanUntilClassName();
		}
		$doc_comment = $this->doc_comment;

		if ($flags) {
			$flip = array_flip($flags);
			if (($this->type !== T_INTERFACE) && isset($flip[T_USE])) {
				foreach ($this->getTraits() as $trait) {
					$doc_comment .= $trait->getDocComment($flags);
				}
			}
			if (($this->type === T_CLASS) && isset($flip[T_EXTENDS])) {
				if ($parent = $this->getParent()) {
					$doc_comment .= $parent->getDocComment($flags);
				}
			}
			if (($this->type !== T_TRAIT) && isset($flip[T_IMPLEMENTS])) {
				foreach ($this->getInterfaces() as $interface) {
					$doc_comment .= $interface->getDocComment($flags);
				}
			}
		}

		return $doc_comment;
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
	 * @param $flags integer[] T_EXTENDS, T_IMPLEMENTS, T_USE
	 * @return Reflection_Method[]
	 */
	public function getMethods($flags = [])
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
						$this->traits_methods = array_merge(
							$trait->getMethods([T_USE]), $this->traits_methods
						);
					}
				}
				$methods = array_merge($this->traits_methods, $methods);
			}
			if (isset($flip[T_EXTENDS])) {
				if (!isset($this->parent_methods)) {
					$this->parent_methods = [];
					if ($parent = $this->getParent()) {
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
	 * @return string
	 */
	public function getNamespaceName()
	{
		if (!isset($this->namespace)) {
			$this->scanUntilClassName();
		}
		return $this->namespace;
	}

	//------------------------------------------------------------------------------------- getParent
	/**
	 * Gets parent Reflection_Class object
	 *
	 * If parent is an internal class, of if there is no parent : will return null.
	 *
	 * @return Reflection_Class
	 */
	public function getParent()
	{
		if (!isset($this->parent)) {
			$this->scanUntilClassBegins();
		}
		if (is_string($this->parent)) {
			$parent = $this->source->getOutsideClass($this->parent);
			if ($parent->source->isInternal()) {
				$this->parent = false;
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
		}
		return is_string($this->parent) ? $this->parent : $this->parent->name;
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
		preg_match($expr, $this->getDocComment(), $match);
		return $match
			? Namespaces::defaultFullClassName($match[1], $this->name)
			: Set::defaultSetClassNameOf($this->name);
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

	//--------------------------------------------------------------------------------- getProperties
	/**
	 * @param $flags integer[] T_EXTENDS, T_USE
	 * @return Reflection_Property[]
	 */
	public function getProperties($flags = [])
	{
		if (!isset($this->properties)) {
			$this->scanUntilClassEnds();
		}
		$properties = $this->properties;

		if ($flags) {
			$flip = array_flip($flags);
			if (isset($flip[T_USE])) {
				if (!isset($this->traits_properties)) {
					$this->traits_properties = [];
					foreach ($this->getTraits() as $trait) {
						$this->traits_properties = array_merge(
							$trait->getProperties([T_USE]), $this->traits_properties
						);
					}
				}
				$properties = array_merge($this->traits_properties, $properties);
			}
			if (isset($flip[T_EXTENDS])) {
				if (!isset($this->parent_properties)) {
					$this->parent_properties = ($parent = $this->getParent())
						? $parent->getProperties([T_EXTENDS, T_USE])
						: [];
				}
				$properties = array_merge($this->parent_properties, $properties);
			}
		}

		return $properties;
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
	 * @return boolean
	 */
	public function inNamespace()
	{
		if (!isset($this->namespace)) {
			$this->scanUntilClassName();
		}
		return $this->namespace ? true : false;
	}

	//------------------------------------------------------------------------------------ isAbstract
	/**
	 * @return boolean
	 */
	public function isAbstract()
	{
		if (!isset($this->name)) {
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

	//----------------------------------------------------------------------------------- isInterface
	/**
	 * @return boolean
	 */
	public function isInterface()
	{
		return $this->type === T_INTERFACE;
	}

	//------------------------------------------------------------------------------------ isInternal
	/**
	 * @return boolean
	 */
	public function isInternal()
	{
		return false;
	}

	//--------------------------------------------------------------------------------------- isFinal
	/**
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
	 * @param $object object
	 * @return boolean
	 */
	public function isInstance($object)
	{
		return is_a($object, $this->name, true);
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
	 * @return boolean
	 */
	public function isUserDefined()
	{
		return true;
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

			$this->getTokens();
			$token = $this->tokens[$this->token_key];
			while ($token !== '{') {
				if (is_array($token) && in_array($token[0], [T_EXTENDS, T_IMPLEMENTS])) {
					foreach ($this->scanClassNames() as $class_name => $line) {
						$class_name = $this->fullClassName($class_name);
						if ($token[0] === T_IMPLEMENTS) {
							$this->interfaces[$class_name] = $class_name;
						}
						else {
							$this->parent = $class_name;
						}
					}
					$token = $this->tokens[$this->token_key];
				}
				else {
					$token = $this->tokens[++$this->token_key];
				}
			}

		}
	}

	//---------------------------------------------------------------------------- scanUntilClassEnds
	/**
	 * Scan tokens until the class ends
	 *
	 */
	private function scanUntilClassEnds()
	{
		if (!isset($this->methods)) {
			$this->scanUntilClassBegins();

			$this->methods    = [];
			$this->properties = [];
			$this->traits     = [];
			unset($this->stop);

			$depth = 0;
			$visibility_token = null;

			$this->getTokens();
			$token = $this->tokens[$this->token_key];
			do {

				switch ($token[0]) {

					case T_USE:
						if ($depth === 1) {
							foreach ($this->scanTraitNames($this->token_key) as $trait_name => $line) {
								$trait_name = $this->fullClassName($trait_name);
								$this->traits[$trait_name] = $trait_name;
							}
						}
						break;

					case T_PUBLIC: case T_PRIVATE: case T_PROTECTED: case T_VAR:
						if ($depth === 1) {
							$visibility_token = $this->token_key;
						}
						break;

					case T_VARIABLE:
						if (($depth === 1) && isset($visibility_token)) {
							$property_name = substr($token[1], 1);
							$visibility = $this->tokens[$visibility_token][0];
							$this->properties[$property_name] = new Reflection_Property(
								$this,
								$property_name,
								$this->tokens[$visibility_token][2],
								$visibility_token,
								($visibility === T_VAR) ? T_PUBLIC : $visibility
							);
						}
						$visibility_token = null;
						break;

					case T_FUNCTION:
						if ($depth === 1) {
							$line = $token[2];
							$token_key = $this->token_key;
							while ($this->tokens[++$this->token_key][0] !== T_STRING);
							$token = $this->tokens[$this->token_key];
							$this->methods[$token[1]] = new Reflection_Method(
								$this, $token[1], $line, $token_key, $visibility_token ?: T_PUBLIC
							);
							$visibility_token = null;
						}
						break;

					case '{':
						$depth ++;
						$visibility_token = null;
						break;

					case '}':
						$depth --;
						if (!$depth) {
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
								$this->namespace = $this->scanClassName($this->token_key);
								$this->use = [];
								break;

							case T_USE:
								foreach ($this->scanClassNames($this->token_key) as $used => $line) {
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

				$class_name = $this->fullClassName($this->scanClassName($this->token_key), false);

			} while (!isset($this->name) || ($class_name !== $this->name));
			$this->name = $class_name;

		}
	}

}
