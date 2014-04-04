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

		unset($this->name);
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
		if (in_array($property_name, ['name', 'type'])) {
			$this->scanUntilClassName();
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
	 * Scan tokens from the current position until the class begins
	 * Current position must be after the class name
	 */
	private function scanUntilClassBegins()
	{
		if (!isset($this->interfaces)) {
			$this->scanUntilClassName();

			$this->interfaces = [];
			$this->parent = null;
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

	//---------------------------------------------------------------------------- scanUntilClassName
	/**
	 * Scan tokens from the current position until class name
	 * This resets the tokens scan to start from the namespace declaration
	 */
	private function scanUntilClassName()
	{
		if (!isset($this->use)) {
			$this->getTokens();
			$token = $this->tokens[$this->token_key = 0];

			$this->namespace = null;
			$this->use = [];
			do {

				$this->doc_comment = '';
				$this->is_abstract = false;
				$this->is_final = false;

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
