<?php
namespace ITRocks\Framework\PHP;

use ITRocks\Framework\Reflection\Annotation\Annoted;
use ITRocks\Framework\Reflection\Attribute\Class_\Extend;
use ITRocks\Framework\Reflection\Attribute\Property\Values;
use ITRocks\Framework\Reflection\Interfaces;
use ITRocks\Framework\Reflection\Interfaces\Has_Doc_Comment;

/**
 * The same as PHP's ReflectionMethod, but working with PHP source, without loading the class
 */
class Reflection_Method implements Has_Doc_Comment, Interfaces\Reflection_Method
{
	use Annoted;

	//----------------------------------------------------------------------------------- $attributes
	/** @var Reflection_Attribute[] */
	private array $attributes;

	//---------------------------------------------------------------------------------------- $class
	public Reflection_Class $class;

	//---------------------------------------------------------------------------------- $doc_comment
	private string $doc_comment;

	//--------------------------------------------------------------------------------------- $indent
	public string $indent;
	
	//---------------------------------------------------------------------------------- $is_abstract
	private bool $is_abstract;

	//------------------------------------------------------------------------------------- $is_final
	private bool $is_final;

	//------------------------------------------------------------------------------------ $is_static
	private bool $is_static;

	//----------------------------------------------------------------------------------------- $line
	public int $line;

	//----------------------------------------------------------------------------------------- $name
	public string $name;

	//----------------------------------------------------------------------------- $parameters_names
	/** @var string[] */
	private array $parameters_names;

	//--------------------------------------------------------------------------------------- $parent
	public Interfaces\Reflection_Method|false|null $parent;

	//----------------------------------------------------------------------------- $prototype_string
	private string $prototype_string;

	//--------------------------------------------------------------------------- $return_type_string
	private string $return_type_string;

	//-------------------------------------------------------------------------------------- $returns
	private string $returns;

	//---------------------------------------------------------------------------- $returns_reference
	private bool $returns_reference;

	//------------------------------------------------------------------------------------ $token_key
	/** The key for the T_FUNCTION token */
	private int $token_key;

	//----------------------------------------------------------------------------------- $visibility
	#[Values(T_PUBLIC, T_PROTECTED, T_PRIVATE)]
	public int $visibility;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class      Reflection_Class
	 * @param $name       string
	 * @param $line       integer
	 * @param $token_key  integer
	 * @param $visibility integer
	 * @param $attributes Reflection_Attribute[]
	 */
	public function __construct(
		Reflection_Class $class, string $name, int $line, int $token_key, int $visibility,
		array $attributes
	) {
		$this->attributes = $attributes;
		$this->class      = $class;
		$this->line       = $line;
		$this->name       = $name;
		$this->token_key  = $token_key;
		$this->visibility = $visibility;
	}

	//----------------------------------------------------------------------------- getDeclaringClass
	public function getDeclaringClass() : Reflection_Class
	{
		return $this->class;
	}

	//------------------------------------------------------------------------- getDeclaringClassName
	/** Gets declaring class name */
	public function getDeclaringClassName() : string
	{
		return $this->class->name;
	}

	//--------------------------------------------------------------------------------- getDocComment
	/**
	 * TODO LOWEST parent methods read
	 *
	 * @param $flags integer[]|null T_EXTENDS, T_IMPLEMENTS, T_USE
	 * @return string
	 */
	public function getDocComment(array|null $flags = []) : string
	{
		if (isset($this->doc_comment)) {
			return $this->doc_comment;
		}
		$this->doc_comment =  '';
		$tokens            =& $this->class->source->getTokens();
		$token_key         =  $this->token_key;
		while (is_array($token = $tokens[--$token_key])) {
			if ($token[0] === T_DOC_COMMENT) {
				$this->doc_comment = $token[1] . $this->doc_comment;
			}
		}
		return $this->doc_comment;
	}

	//------------------------------------------------------------------------------------- getIndent
	public function getIndent() : string
	{
		if (isset($this->indent)) {
			return $this->indent;
		}
		$tokens    =& $this->class->source->getTokens();
		$token_key =  $this->token_key;
		/** @noinspection PhpStatementHasEmptyBodyInspection not really empty (--) */
		while (is_array($token = $tokens[--$token_key]) && !str_contains($token[1], LF));
		$this->indent = is_array($token) ? $token[1] : '';
		return $this->indent;
	}

	//--------------------------------------------------------------------------------------- getName
	public function getName() : string
	{
		return $this->name;
	}

	//----------------------------------------------------------------------------- getParametersCall
	/** @return string A calling string for parameters call e.g. '$param1, $param2, $param3' */
	public function getParametersCall() : string
	{
		$parameters_names = $this->getParametersNames();
		return $parameters_names ? ('$' . join(', $', $this->getParametersNames())) : '';
	}

	//---------------------------------------------------------------------------- getParametersNames
	/**
	 * @param $by_name boolean
	 * @return string[] key and value are both the parameter name
	 */
	public function getParametersNames(bool $by_name = true) : array
	{
		if (isset($this->parameters_names)) {
			return $this->parameters_names;
		}
		$counter                = 0;
		$this->parameters_names =  [];
		$tokens                 =& $this->class->source->getTokens();
		$token_key              =  $this->token_key;
		/** @noinspection PhpStatementHasEmptyBodyInspection ++ in condition */
		while (($tokens[++$token_key]) !== '(');
		while (($token = $tokens[++$token_key]) !== ')') {
			if ($token[0] === T_VARIABLE) {
				$parameter_name = substr($token[1], 1);
				$this->parameters_names[$by_name ? $parameter_name : $counter++] = $parameter_name;
			}
		}
		return $this->parameters_names;
	}

	//------------------------------------------------------------------------------------- getParent
	public function getParent() : ?Interfaces\Reflection_Method
	{
		if (isset($this->parent)) {
			return $this->parent ?: null;
		}
		$this->parent = false;
		$class_parent = $this->class->getParentClass();
		if (!$class_parent) {
			return null;
		}
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
		return $this->parent ?: null;
	}

	//---------------------------------------------------------------------------- getPrototypeString
	/**
	 * The prototype of the function, beginning with first whitespaces before function and its doc
	 * comments, ending with '{' or ';' followed by LF.
	 */
	public function getPrototypeString() : string
	{
		if (isset($this->prototype_string)) {
			return $this->prototype_string;
		}
		$this->prototype_string = '';
		$tokens    =& $this->class->source->getTokens();
		$token_key =  $this->token_key;
		while (is_array($token = $tokens[--$token_key])) {
			if ($token[0] === T_WHITESPACE) {
				$token[1] = str_replace([CR, LF . LF], ['', LF], $token[1]);
			}
			$this->prototype_string = $token[1] . $this->prototype_string;
		}
		$token_key = $this->token_key;
		while (!in_array($token = $tokens[$token_key++], [';', '{'])) {
			$this->prototype_string .= is_array($token) ? $token[1] : $token;
		}
		$this->prototype_string .= $token . LF;
		return $this->prototype_string;
	}

	//--------------------------------------------------------------------------- getReturnTypeString
	public function getReturnTypeString() : string
	{
		if (isset($this->return_type_string)) {
			return $this->return_type_string ?: 'void';
		}
		$this->return_type_string =  '';
		$get_type                 =  false;
		$tokens                   =& $this->class->source->getTokens();
		$token_key                =  $this->token_key;
		/** @noinspection PhpStatementHasEmptyBodyInspection ++ in condition */
		while (($tokens[++$token_key]) !== '(');
		/** @noinspection PhpStatementHasEmptyBodyInspection ++ in condition */
		while (($tokens[++$token_key]) !== ')');
		while (($token = $tokens[++$token_key]) !== '{') {
			if ($get_type) {
				$this->return_type_string .= is_array($token) ? $token[1] : $token;
			}
			if ($token === ':') $get_type = true;
		}
		$this->return_type_string = trim($this->return_type_string);
		return $this->return_type_string ?: 'void';
	}

	//------------------------------------------------------------------------------------ isAbstract
	public function isAbstract() : bool
	{
		if (isset($this->is_abstract)) {
			return $this->is_abstract;
		}
		$this->is_abstract = ($this->class->type === T_INTERFACE);
		if (!$this->is_abstract) {
			$this->scanBefore();
		}
		return $this->is_abstract;
	}

	//--------------------------------------------------------------------------------- isConstructor
	public function isConstructor() : bool
	{
		return ($this->name === '__construct')
			|| ($this->name === rLastParse($this->class->name, BS, 1, true));
	}

	//---------------------------------------------------------------------------------- isDestructor
	public function isDestructor() : bool
	{
		return ($this->name === '__destruct');
	}

	//--------------------------------------------------------------------------------------- isFinal
	public function isFinal() : bool
	{
		if (!isset($this->is_final)) {
			$this->scanBefore();
		}
		return $this->is_final;
	}

	//------------------------------------------------------------------------------------ isInternal
	public function isInternal() : bool
	{
		return false;
	}

	//------------------------------------------------------------------------------------- isPrivate
	public function isPrivate() : bool
	{
		return $this->visibility === T_PRIVATE;
	}

	//----------------------------------------------------------------------------------- isProtected
	public function isProtected() : bool
	{
		return $this->visibility === T_PROTECTED;
	}

	//-------------------------------------------------------------------------------------- isPublic
	public function isPublic() : bool
	{
		return $this->visibility === T_PUBLIC;
	}

	//-------------------------------------------------------------------------------------- isStatic
	public function isStatic() : bool
	{
		if (!isset($this->is_static)) {
			$this->scanBefore();
		}
		return $this->is_static;
	}

	//--------------------------------------------------------------------------------- isUserDefined
	public function isUserDefined() : bool
	{
		return true;
	}

	//-------------------------------------------------------------------------------------------- of
	/**
	 * @param $class_name  string
	 * @param $method_name string
	 * @param $flags       integer[] @values T_EXTENDS, T_IMPLEMENTS, T_USE
	 * @return Reflection_Method
	 */
	public static function of(string $class_name, string $method_name, array $flags = [])
		: Reflection_Method
	{
		$class   = Reflection_Class::of($class_name);
		$methods = $class->getMethods($flags);
		if (isset($methods[$method_name]) && in_array(T_EXTENDS, $flags)) {
			return $methods[$method_name];
		}
		do {
			$extends = Extend::oneOf($class)->extends;
			$class   = $extends ? $class->source->getOutsideClass($extends[0]) : null;
			$methods = $class?->getMethods($flags);
		}
		while ($class && !isset($methods[$method_name]));
		if (!isset($methods[$method_name])) {
			trigger_error('Method not found ' . $class_name . '::' . $method_name, E_USER_ERROR);
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
	 */
	public static function regex(string $method_name = '') : string
	{
		$name = $method_name ?: '\w+';
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
	public function returns() : string
	{
		if (isset($this->returns)) {
			return $this->returns;
		}
		$expr = '%\*\s+@return\s+([\\\\\w]+)%';
		preg_match($expr, $this->getDocComment(), $match);
		if ($match) {
			$this->returns = $match[1];
		}
		else {
			$this->returns = $this->getReturnTypeString();
			if ($this->returns === 'void') {
				$this->returns = '';
			}
		}
		return $this->returns;
	}

	//------------------------------------------------------------------------------ returnsReference
	public function returnsReference() : bool
	{
		if (isset($this->returns_reference)) {
			return $this->returns_reference;
		}
		$tokens     =& $this->class->source->getTokens();
		$token_key  =  $this->token_key + 1;
		$ampersands =  [
			T_AMPERSAND_FOLLOWED_BY_VAR_OR_VARARG,
			T_AMPERSAND_NOT_FOLLOWED_BY_VAR_OR_VARARG
		];
		while (is_array($token = $tokens[$token_key]) && !in_array($token[0], $ampersands)) {
			$token_key ++;
		}
		$this->returns_reference = is_array($token) || ($token === '&');
		return $this->returns_reference;
	}

	//------------------------------------------------------------------------------------ scanBefore
	private function scanBefore() : void
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
