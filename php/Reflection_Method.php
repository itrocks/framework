<?php
namespace ITRocks\Framework\PHP;

use ITRocks\Framework\Reflection\Annotation\Annoted;
use ITRocks\Framework\Reflection\Annotation\Class_\Extends_Annotation;
use ITRocks\Framework\Reflection\Interfaces;
use ITRocks\Framework\Reflection\Interfaces\Has_Doc_Comment;

/**
 * The same as PHP's ReflectionMethod, but working with PHP source, without loading the class
 */
class Reflection_Method implements Has_Doc_Comment, Interfaces\Reflection_Method
{
	use Annoted;

	//---------------------------------------------------------------------------------------- $class
	/**
	 * @var $class Reflection_Class
	 */
	public $class;

	//---------------------------------------------------------------------------------- $doc_comment
	/**
	 * @var string
	 */
	private $doc_comment;

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

	//------------------------------------------------------------------------------------ $is_static
	/**
	 * @var boolean
	 */
	private $is_static;

	//----------------------------------------------------------------------------------------- $line
	/**
	 * @var integer
	 */
	public $line;

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @var string
	 */
	public $name;

	//----------------------------------------------------------------------------- $prototype_string
	/**
	 * @var string
	 */
	private $prototype_string;

	//-------------------------------------------------------------------------------------- $returns
	/**
	 * @var boolean
	 */
	private $returns;

	//---------------------------------------------------------------------------- $returns_reference
	/**
	 * @var boolean
	 */
	private $returns_reference;

	//------------------------------------------------------------------------------------ $token_key
	/**
	 * The key for the T_FUNCTION token
	 *
	 * @var integer
	 */
	private $token_key;

	//----------------------------------------------------------------------------------- $visibility
	/**
	 * @values T_PUBLIC, T_PROTECTED, T_PRIVATE
	 * @var integer
	 */
	public $visibility;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class      Reflection_Class
	 * @param $name       string
	 * @param $line       integer
	 * @param $token_key  integer
	 * @param $visibility integer
	 */
	public function __construct(Reflection_Class $class, $name, $line, $token_key, $visibility)
	{
		$this->class      = $class;
		$this->line       = $line;
		$this->name       = $name;
		$this->token_key  = $token_key;
		$this->visibility = $visibility;
	}

	//----------------------------------------------------------------------------- getDeclaringClass
	/**
	 * @return Reflection_Class
	 */
	public function getDeclaringClass()
	{
		return $this->class;
	}

	//------------------------------------------------------------------------- getDeclaringClassName
	/**
	 * Gets declaring class name
	 *
	 * @return string
	 */
	public function getDeclaringClassName()
	{
		return $this->class->name;
	}

	//--------------------------------------------------------------------------------- getDocComment
	/**
	 * TODO LOWEST parent methods read
	 *
	 * @param $flags integer[]|boolean T_EXTENDS, T_IMPLEMENTS, T_USE
	 * @return string
	 */
	public function getDocComment(array $flags = [])
	{
		if (!isset($this->doc_comment)) {
			$this->doc_comment =  '';
			$tokens            =& $this->class->source->getTokens();
			$token_key         =  $this->token_key;
			while (is_array($token = $tokens[--$token_key])) {
				if ($token[0] === T_DOC_COMMENT) {
					$this->doc_comment = $token[1] . $this->doc_comment;
				}
			}
		}
		return $this->doc_comment;
	}

	//------------------------------------------------------------------------------------- getIndent
	/**
	 * @return string
	 */
	public function getIndent()
	{
		if (!isset($this->indent)) {
			$tokens    =& $this->class->source->getTokens();
			$token_key =  $this->token_key;
			/** @noinspection PhpStatementHasEmptyBodyInspection not really empty (--) */
			while (is_array($token = $tokens[--$token_key]) && (strpos($token[1], LF) === false));
			$this->indent = is_array($token) ? $token[1] : '';
		}
		return $this->indent;
	}

	//--------------------------------------------------------------------------------------- getName
	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	//----------------------------------------------------------------------------- getParametersCall
	/**
	 * Return a calling string for parameters call
	 *
	 * @return string ie '$param1, $param2, $param3'
	 */
	public function getParametersCall()
	{
		$parameters_names = $this->getParametersNames();
		return $parameters_names ? ('$' . join(', $', $this->getParametersNames())) : '';
	}

	//---------------------------------------------------------------------------- getParametersNames
	/**
	 * @param $by_name boolean
	 * @return string[] key and value are both the parameter name
	 */
	public function getParametersNames($by_name = true)
	{
		$counter = 0;
		if (!isset($this->parameters_names)) {
			$this->parameters_names =  [];
			$tokens                 =& $this->class->source->getTokens();
			$token_key              =  $this->token_key;
			/** @noinspection PhpStatementHasEmptyBodyInspection ++ in condition */
			while (($token = $tokens[++$token_key]) !== '(');
			while (($token = $tokens[++$token_key]) !== ')') {
				if ($token[0] === T_VARIABLE) {
					$parameter_name = substr($token[1], 1);
					$this->parameters_names[$by_name ? $parameter_name : $counter++] = $parameter_name;
				}
			}
		}
		return $this->parameters_names;
	}

	//------------------------------------------------------------------------------------- getParent
	/**
	 * @return Reflection_Method
	 */
	public function getParent()
	{
		if (!isset($this->parent)) {
			$this->parent = false;
			$class_parent = $this->class->getParentClass();
			if ($class_parent) {
				$methods = $class_parent->getMethods();
				if (!isset($methods[$this->name])) {
					$methods = $class_parent->getMethods([T_USE]);
					if (!isset($methods[$this->name])) {
						$methods = $class_parent->getMethods([T_EXTENDS]);
						if (!isset($methods[$this->name])) {
							$methods = $class_parent->getMethods([T_IMPLEMENTS]);
						}
					}
				}
				if (isset($methods[$this->name])) {
					$this->parent = $methods[$this->name];
				}
			}
		}
		return $this->parent ?: null;
	}

	//---------------------------------------------------------------------------- getPrototypeString
	/**
	 * The prototype of the function, beginning with first whitespaces before function and its doc
	 * comments, ending with { or ; followed by LF.
	 *
	 * @return string
	 */
	public function getPrototypeString()
	{
		if (!isset($this->prototype_string)) {
			$this->prototype_string = '';
			$tokens    =& $this->class->source->getTokens();
			$token_key =  $this->token_key;
			while (is_array($token = $tokens[--$token_key])) {
				if ($token[0] == T_WHITESPACE) {
					$token[1] = str_replace([CR, LF . LF], ['', LF], $token[1]);
				}
				$this->prototype_string = $token[1] . $this->prototype_string;
			}
			$token_key = $this->token_key;
			while (!in_array($token = $tokens[$token_key++], [';', '{'])) {
				$this->prototype_string .= is_array($token) ? $token[1] : $token;
			}
			$this->prototype_string .= $token . LF;
		}
		return $this->prototype_string;
	}

	//------------------------------------------------------------------------------------ isAbstract
	/**
	 * @return boolean
	 */
	public function isAbstract()
	{
		if (!isset($this->is_abstract)) {
			$this->is_abstract = ($this->class->type === T_INTERFACE);
			if (!$this->is_abstract) {
				$this->scanBefore();
			}
		}
		return $this->is_abstract;
	}

	//--------------------------------------------------------------------------------- isConstructor
	/**
	 * @return boolean
	 */
	public function isConstructor()
	{
		return ($this->name === '__construct')
			|| ($this->name === rLastParse($this->class->name, BS, 1, true));
	}

	//---------------------------------------------------------------------------------- isDestructor
	/**
	 * @return boolean
	 */
	public function isDestructor()
	{
		return ($this->name === '__destruct');
	}

	//--------------------------------------------------------------------------------------- isFinal
	/**
	 * @return boolean
	 */
	public function isFinal()
	{
		if (!isset($this->is_final)) {
			$this->scanBefore();
		}
		return $this->is_final;
	}

	//------------------------------------------------------------------------------------ isInternal
	/**
	 * @return boolean
	 */
	public function isInternal()
	{
		return false;
	}

	//------------------------------------------------------------------------------------- isPrivate
	/**
	 * @return boolean
	 */
	public function isPrivate()
	{
		return $this->visibility === T_PRIVATE;
	}

	//----------------------------------------------------------------------------------- isProtected
	/**
	 * @return boolean
	 */
	public function isProtected()
	{
		return $this->visibility === T_PROTECTED;
	}

	//-------------------------------------------------------------------------------------- isPublic
	/**
	 * @return boolean
	 */
	public function isPublic()
	{
		return $this->visibility === T_PUBLIC;
	}

	//-------------------------------------------------------------------------------------- isStatic
	/**
	 * @return boolean
	 */
	public function isStatic()
	{
		if (!isset($this->is_static)) {
			$this->scanBefore();
		}
		return $this->is_static;
	}

	//--------------------------------------------------------------------------------- isUserDefined
	/**
	 * @return boolean
	 */
	public function isUserDefined()
	{
		return true;
	}

	//-------------------------------------------------------------------------------------------- of
	/**
	 * @param $class_name  string
	 * @param $method_name string
	 * @param $flags       integer[] T_EXTENDS, T_IMPLEMENTS, T_USE
	 * @return Reflection_Method
	 */
	public static function of($class_name, $method_name, array $flags = [])
	{
		$class   = Reflection_Class::of($class_name);
		$methods = $class->getMethods($flags);
		if (!isset($methods[$method_name]) && in_array(T_EXTENDS, $flags)) {
			do {
				$extends = Extends_Annotation::of($class)->values();
				$class   = $extends ? $class->source->getOutsideClass($extends[0]) : null;
				$methods = $class   ? $class->getMethods($flags)                   : null;
			}
			while ($class && !isset($methods[$method_name]));
			if (!isset($methods[$method_name])) {
				trigger_error('Method not found ' . $class_name . '::' . $method_name, E_USER_ERROR);
			}
		}
		return $methods[$method_name];
	}

	//----------------------------------------------------------------------------------------- regex
	/**
	 * Gets the preg expression to find a method in PHP source
	 * If no method name is given, the preg expression to find all methods in source is returned
	 *
	 * Preg matching records will be :
	 * - 0 : the full method prototype
	 * - 1 : indent characters (including '\n')
	 * - 2 : the last phpdocumentor documentation before the function keyword
	 * - 3 : 'abstract' or empty
	 * - 4 : 'private', 'protected', 'public' or empty
	 * - 5 : 'static' or empty
	 * - 6 : '&' or empty
	 * - 7 : the name of the method
	 * - 8 : the parameters string
	 * - 9 : the end character of the function prototype : ';' for abstract functions, or '{'
	 *
	 * @param $method_name string
	 * @return string
	 */
	public static function regex($method_name = null)
	{
		$name = isset($method_name) ? $method_name : '\w+';
		return '%'
			. '(\n\s*)?'                            // 1 : indent
			. '(/\*\*\n(?:\s*\*.*\n)*\s*\*/\n\s*)?' // 2 : documentation
			. '(?:\/\*.*\*/\n\s*)?'                 // ignored one-line documentation
			. '(abstract\s+)?'                      // 3 : abstract
			. '(?:(private|protected|public)\s+)?'  // 4 : visibility
			. '(static\s+)?'                        // 5 : static
			. 'function\s+'                         // function keyword
			. '(\&\s*)?'                            // 6 : reference flag
			. '(' . $name . ')\s*'                  // 7 : name
			. '(\((?:.*?\n?)*?\)\s*)'               // 8 : parameters string
			. '([\{\;]\s*?\n)'                      // 9 : end of function prototype
			. '%';
	}

	//--------------------------------------------------------------------------------------- returns
	/**
	 * @return string
	 */
	public function returns()
	{
		if (!isset($this->returns)) {
			$expr = '%'
				. '\n\s*\*\s+\@return\s+([\\\\\w]+)'
				. '%';
			preg_match($expr, $this->getDocComment(), $match);
			$this->returns = $match ? $match[1] : false;
		}
		return $this->returns ?: null;
	}

	//------------------------------------------------------------------------------ returnsReference
	/**
	 * @return boolean
	 */
	public function returnsReference()
	{
		if (!isset($this->returns_reference)) {
			$tokens    =& $this->class->source->getTokens();
			$token_key =  $this->token_key;
			/** @noinspection PhpStatementHasEmptyBodyInspection ++ in condition */
			while (is_array($token = $tokens[++$token_key]));
			$this->returns_reference = ($token === '&');
		}
		return $this->returns_reference;
	}

	//------------------------------------------------------------------------------------ scanBefore
	private function scanBefore()
	{
		// don't initialise $this->is_abstract here ! this is done by isAbstract()
		$this->is_final  = false;
		$this->is_static = false;
		$tokens    =& $this->class->source->getTokens();
		$token_key =  $this->token_key;
		while (is_array($token = $tokens[--$token_key])) {
			switch ($token[0]) {
				case T_ABSTRACT: $this->is_abstract = true; break;
				case T_FINAL:    $this->is_final    = true; break;
				case T_STATIC :  $this->is_static   = true; break;
			}
		}
	}

}
