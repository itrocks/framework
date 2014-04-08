<?php
namespace SAF\PHP;

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
	private $namespace;

	//------------------------------------------------------------------------------------ $token_key
	/**
	 * The current key into tokens, used by parser to know what it did parse or not
	 *
	 * @var integer
	 */
	private $token_key;

	//--------------------------------------------------------------------------------------- $tokens
	/**
	 * PHP tokens array
	 *
	 * @see token_get_all()
	 * @var array
	 */
	private $tokens;

	//------------------------------------------------------------------------------------------ $use
	/**
	 * Namespaces and class names used by current namespace
	 *
	 * @var integer[] key is the used class name or namespace, value is the declaration line number
	 */
	private $use;

	//---------------------------------------------------------------------------------- currentToken
	/**
	 * @return array|string
	 */
	private function currentToken()
	{
		return $this->tokens[$this->token_key];
	}

	//--------------------------------------------------------------------------------- fullClassName
	/**
	 * Resolves the full class name for any class name in current source code context
	 *
	 * @param $class_name string the class name we want to get the full class name
	 * @param $use        boolean use the 'use' clause linked to the namespace
	 * @return string
	 */
	private function fullClassName($class_name, $use = true)
	{
		// class name beginning with '\' : this is the full class name
		if ($class_name[0] === BS) {
			return substr($class_name, 1);
		}
		// class name containing '\' : search for namespace
		if ($length = strpos($class_name, BS)) {
			$search = BS . substr($class_name, 0, $length++);
			if ($use) foreach ($this->use as $u) {
				$bu = BS . $u;
				if (substr($bu, -$length) === $search) {
					return ((strlen($bu) > $length) ? (substr($bu, 1, -$length) . BS) : '') . $class_name;
				}
			}
			return ($this->namespace ? ($this->namespace . BS) : '') . $class_name;
		}
		if ($use) {
			// class name without '\' : search for full class name
			$search = BS . $class_name;
			$length = strlen($search);
			if ($use) foreach ($this->use as $u) {
				$bu = BS . $u;
				if(substr($bu, -$length) === $search) {
					return $u;
				}
			}
		}
		return ($this->namespace ? ($this->namespace . BS) : '') . $class_name;
	}

	//------------------------------------------------------------------------------------- nextToken
	/**
	 * @return array|string
	 */
	private function nextToken()
	{
		return $this->tokens[++$this->token_key];
	}

	//--------------------------------------------------------------------------------- previousToken
	/**
	 * @return array|string
	 */
	private function previousToken()
	{
		return $this->tokens[--$this->token_key];
	}

	//--------------------------------------------------------------------------------- scanClassName
	/**
	 * Scans a class name : works with 'Class_Name' and 'Has\Namespace\Class_Name'
	 * Starts from the next token
	 *
	 * @return string
	 */
	private function scanClassName()
	{
		$class_name = '';
		do {
			$token = $this->tokens[++$this->token_key];
		} while ($token[0] === T_WHITESPACE);
		while (in_array($token[0], [T_NS_SEPARATOR, T_STRING])) {
			$class_name .= $token[1];
			$token = $this->tokens[++$this->token_key];
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
	private function scanClassNames()
	{
		$class_names = [];
		$line = 0;
		$used = '';
		do {
			$token = $this->tokens[++$this->token_key];
			if (is_array($token)) {
				if (in_array($token[0], [T_NS_SEPARATOR, T_STRING])) {
					$line = $token[2];
					$used .= $token[1];
					$continue = true;
				}
				else {
					$continue = ($token[0] === T_WHITESPACE);
				}
			}
			elseif ($token === ',') {
				$class_names[$used] = $line;
				$used = '';
				$continue = true;
			}
			else {
				$continue = false;
			}
		} while ($continue);
		if ($used) {
			$class_names[$used] = $line;
		}
		return $class_names;
	}

	//-------------------------------------------------------------------------------- scanTraitNames
	/**
	 * Scans commas separated trait names. Ignore { } traits details
	 *
	 * @return integer[] key is the trait name, value is the line number it was declared
	 */
	private function scanTraitNames()
	{
		$trait_names = [];
		$trait_name = '';
		$depth = 0;
		$line = 0;
		do {
			$token = $this->tokens[++$this->token_key];
			if ($token === ',') {
				$trait_names[$trait_name] = $line;
				$trait_name = '';
			}
			else {
				$token_id = $token[0];
				if ($token_id == '{') {
					$depth ++;
				}
				elseif ($token_id == '}') {
					$depth --;
					if (!$depth) {
						break;
					}
				}
				elseif (in_array($token_id, [T_NS_SEPARATOR, T_STRING]) && !$depth) {
					$trait_name .= $token[1];
					$line = $token[2];
				}
			}
		} while ($depth || ($token !== ';'));
		if ($trait_name) {
			$trait_names[$trait_name] = $line;
		}
		return $trait_names;
	}

}
