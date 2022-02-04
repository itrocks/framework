<?php
namespace ITRocks\Framework\PHP;

use ITRocks\Framework\Reflection\Interfaces;
use ITRocks\Framework\Reflection\Type;
use ITRocks\Framework\Tools\Call_Stack;
use JetBrains\PhpStorm\NoReturn;

/**
 * These are helpers functions to parse tokens
 */
trait Tokens_Parser
{

	//------------------------------------------------------------------------------------ $namespace
	/**
	 * The current namespace name
	 *
	 * @var string
	 */
	private string $namespace;

	//---------------------------------------------------------------------------------- $token_debug
	/**
	 * Token key for debugging
	 *
	 * @var integer
	 */
	private int $token_debug;

	//------------------------------------------------------------------------------------ $token_key
	/**
	 * The current key into tokens, used by parser to know what it did parse or not
	 *
	 * @var integer
	 */
	private int $token_key = 0;

	//--------------------------------------------------------------------------------------- $tokens
	/**
	 * PHP tokens array
	 *
	 * @see token_get_all()
	 * @var ?array
	 */
	private ?array $tokens;

	//------------------------------------------------------------------------------------------ $use
	/**
	 * Namespaces and class names used by current namespace
	 *
	 * @var ?integer[] key is the used class name or namespace, value is the declaration line number
	 */
	private ?array $use;

	//-------------------------------------------------------------------------------------- eofError
	/**
	 * @param $method string
	 */
	private function eofError(string $method)
	{
		// display current object to know more about the execution context (ie which file ?)
		echo '<PRE>';
		foreach (get_object_vars($this) as $key => $value) {
			if (!is_array($value) && !is_object($value)) {
				echo $key . ' = ' . htmlentities(strval($value)) . LF;
			}
		}
		echo '</PRE>';
		// calculate context
		$where = '';
		$context = array_slice($this->tokens, $this->token_debug);
		array_walk_recursive($context, function(&$value) use(&$where) {
			if (is_string($value)) {
				$where .= $value;
			}
		});
		// trigger error
		foreach ((new Call_Stack())->lines() as $line) {
			if ($line->object instanceof Interfaces\Reflection_Class) {
				echo '! You may check your class ' . $line->object->getName()
					. ' / file ' . $this->file_name . BR . LF;
			}
		}
		trigger_error('EOF during ' . $method . '() after [' . lParse($where, LF) . ']', E_USER_ERROR);
	}

	//--------------------------------------------------------------------------------- fullClassName
	/**
	 * Resolves the full class name for any class name in current source code context
	 *
	 * @param $class_name string the class name we want to get the full class name
	 * @param $use        boolean use the 'use' clause linked to the namespace
	 * @return string
	 */
	public function fullClassName(string $class_name, bool $use = true) : string
	{
		return (new Type($class_name))->applyNamespace(
			$this->namespace, $use ? array_keys($this->use) : []
		);
	}

	//--------------------------------------------------------------------------------- scanClassName
	/**
	 * Scans a class name : works with 'Class_Name' and 'Has\Namespace\Class_Name'
	 * Starts from the next token
	 *
	 * @return string
	 */
	private function scanClassName() : string
	{
		$this->token_debug = $this->token_key;
		$class_name        = '';
		do {
			$token = $this->tokens[++$this->token_key];
		}
		while (($token[0] === T_WHITESPACE) && isset($this->tokens[$this->token_key + 1]));
		if ($token[0] === T_WHITESPACE) {
			$this->eofError('scanClassName');
		}
		while (in_array($token[0], CLASS_NAME_TOKENS)) {
			$class_name .= $token[1];
			$token       = $this->tokens[++$this->token_key];
		}
		return $class_name;
	}

	//-------------------------------------------------------------------------------- scanClassNames
	/**
	 * Scans class names separated by commas : works with 'Class_Name' and 'Has\Namespace\Class_Name'
	 * Starts from the next token
	 *
	 * @return string[]
	 */
	private function scanClassNames() : array
	{
		$this->token_debug = $this->token_key;
		$class_names       = [];
		$line              = 0;
		$used              = '';
		do {
			$token = $this->tokens[++$this->token_key];
			if (is_array($token)) {
				if (in_array($token[0], CLASS_NAME_TOKENS)) {
					$line  = $token[2];
					$used .= $token[1];
					$continue = true;
				}
				else {
					$continue = in_array($token[0], [T_COMMENT, T_DOC_COMMENT, T_WHITESPACE]);
				}
			}
			elseif ($token === ',') {
				$class_names[$used] = $line;
				$used               = '';
				$continue           = true;
			}
			else {
				$continue = false;
			}
		}
		while ($continue && isset($this->tokens[$this->token_key + 1]));
		if ($token[0] === T_IMPLEMENTS) {
			$this->token_key --;
		}
		if ($continue) {
			$this->eofError('scanClassNames');
		}
		if ($used) {
			$class_names[$used] = $line;
		}
		return $class_names;
	}

	//--------------------------------------------------------------------------- scanRequireFilePath
	/**
	 * Scans ('File path'), "File path" and variants to get the 'file path' value
	 * Can be a PHP expression like '__DIR__ . "File path"' : the resulting string will be kept as this
	 *
	 * @return string
	 */
	private function scanRequireFilePath() : string
	{
		$this->token_debug = $this->token_key;
		$file_path         = '';
		$this->token_key   ++;
		do {
			$token = $this->tokens[$this->token_key++];
			if ($token !== ';') {
				$file_path .= is_array($token) ? $token[1] : $token;
			}
		}
		while (($token !== ';') && ($token !== ')') && isset($this->tokens[$this->token_key]));
		if (($token !== ';') && ($token !== ')')) {
			$this->eofError('scanRequireFilePath');
		}
		return $file_path;
	}

	//-------------------------------------------------------------------------------- scanTraitNames
	/**
	 * Scans commas separated trait names. Ignore doc-comments and { } traits details
	 *
	 * @return integer[] key is the trait name, value is the line number it was declared
	 */
	private function scanTraitNames() : array
	{
		$this->token_debug = $this->token_key;
		$trait_names       = [];
		$trait_name        = '';
		$depth             = 0;
		$line              = 0;
		do {
			$token = $this->tokens[++$this->token_key];
			if ($token === ',') {
				$trait_names[$trait_name] = $line;
				$trait_name               = '';
			}
			else {
				$token_id = $token[0];
				if ($token_id === '{') {
					$depth ++;
				}
				elseif ($token_id === '}') {
					$depth --;
					if (!$depth) {
						break;
					}
				}
				elseif (in_array($token_id, CLASS_NAME_TOKENS) && !$depth) {
					$trait_name .= $token[1];
					$line        = $token[2];
				}
			}
		} while (
			($depth || (($token !== ';') && ($token !== '{')))
			&& isset($this->tokens[$this->token_key + 1])
		);
		if (!isset($this->tokens[$this->token_key + 1])) {
			$this->eofError('scanTraitNames');
		}
		if ($trait_name) {
			$trait_names[$trait_name] = $line;
		}
		return $trait_names;
	}

}

define('CLASS_NAME_TOKENS', (PHP_VERSION < '8')
	? [T_NS_SEPARATOR, T_STRING]
	: [265, 312, 314, T_NS_SEPARATOR, T_STRING]
);
