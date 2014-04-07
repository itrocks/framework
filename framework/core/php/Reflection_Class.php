<?php
namespace SAF\PHP;

//-------------------------------------------------------------------------------- Reflection_Class
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

	//----------------------------------------------------------------------------------- $properties
	/**
	 * @var Reflection_Property[]
	 */
	private $properties;

	//----------------------------------------------------------------------------------------- $stop
	/**
	 * @var integer the line where the class declaration stops into source
	 */
	public $stop;

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

	//--------------------------------------------------------------------------------------- $source
	/**
	 * The PHP source reflection object containing the class
	 *
	 * @var Reflection_Source
	 */
	private $source;

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

	//--------------------------------------------------------------------------------- getInterfaces
	/**
	 * @return Reflection_Class[]
	 */
	public function getInterfaces()
	{
		if (!isset($this->interfaces)) {
			$this->scanUntilClassBegins();
			foreach ($this->interfaces as $interface_name => $interface) {
				if (is_string($interface)) {
					$this->interfaces[$interface_name] = $this->source->getOutsideClass($interface);
				}
			}
		}
		return $this->interfaces;
	}

	//------------------------------------------------------------------------------------- getParent
	/**
	 * @return Reflection_Class
	 */
	public function getParent()
	{
		$this->scanUntilClassBegins();
		if (is_string($this->parent)) {
			$this->parent = $this->source->getOutsideClass($this->parent);
		}
		return $this->parent;
	}

	//--------------------------------------------------------------------------------- getDocComment
	/**
	 * @return string
	 */
	public function getDocComment()
	{
		$this->scanUntilClassName();
		return $this->doc_comment;
	}

	//--------------------------------------------------------------------------------------- getStop
	public function getStop()
	{
		if (!isset($this->stop)) {
			$this->scanUntilClassEnds();
		}
	}

	//------------------------------------------------------------------------------------ getMethods
	/**
	 * @return Reflection_Method[]
	 */
	public function getMethods()
	{
		if (!isset($this->methods)) {
			$this->scanUntilClassEnds();
		}
		return $this->methods;
	}

	//--------------------------------------------------------------------------------- getProperties
	/**
	 * @return Reflection_Property[]
	 */
	public function getProperties()
	{
		if (!isset($this->properties)) {
			$this->scanUntilClassEnds();
		}
		return $this->properties;
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

	//------------------------------------------------------------------------------------ isAbstract
	/**
	 * @return boolean
	 */
	public function isAbstract()
	{
		if (!isset($this->name)) $this->name;
		return $this->is_abstract;
	}

	//--------------------------------------------------------------------------------------- isFinal
	/**
	 * @return boolean
	 */
	public function isFinal()
	{
		if (!isset($this->name)) $this->name;
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

	//----------------------------------------------------------------------------------- isNamespace
	/**
	 * @return boolean
	 */
	public function isNamespace()
	{
		return isset($this->namespace);
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

			$token = $this->tokens[$this->token_key];
			while ($token !== '{') {
				if (is_array($token) && in_array($token[0], [T_EXTENDS, T_IMPLEMENTS])) {
					foreach ($this->source->scanClassNames($this->token_key) as $class_name => $line) {
						$class_name = $this->source->fullClassName($class_name);
						if ($token[0] === T_IMPLEMENTS) {
							$this->interfaces[$class_name] = $class_name;
						}
						else {
							$this->parent = $class_name;
						}
					}
				}
				$token = $this->tokens[++$this->token_key];
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
			$visibility = false;
			$token = $this->tokens[$this->token_key];
			do {

				switch ($token[0]) {

					case T_PUBLIC: case T_PRIVATE: case T_PROTECTED: case T_VAR:
						$visibility = $token[0];
						break;

					case '$':
						if ($visibility) {
							$property_name = $this->scanClassName();
							$this->properties[$property_name] = new Reflection_Property(
								$this->source, $property_name, $this->tokens[$this->token_key - 1][2]
							);
						}
						$visibility = false;
						break;

					case T_FUNCTION:
						$line = $token[2];
						while (($token = $this->tokens[++$this->token_key]) != T_STRING);
						$this->methods[$token[1]] = new Reflection_Method($this->source, $token[1], $line);
						break;

					case ';':
						$visibility = false;
						break;

					case '{':
						$depth ++;
						$visibility = false;
						break;

					case '}':
						$depth --;
						if (!$depth) {
							$end = count($this->tokens);
							$this->token_key ++;
							while (($this->token_key < $end) && !isset($this->stop)) {
								$token = $this->tokens[$this->token_key++];
								if (is_array($token)) {
									$this->stop = $token[2] - 1;
								}
							}
						}
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

			$this->namespace = null;
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
									$this->use[$used] = $line;
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
							case T_WHITESPACE:
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

			} while (!isset($this->name) || ($class_name === $this->name));
			$this->name = $class_name;

		}
	}

}
