<?php
namespace ITRocks\Framework\PHP\Dependency;

use ITRocks\Framework\Reflection\Attribute\Class_\Extend;
use ITRocks\Framework\Reflection\Attribute\Class_\Implement;

class Tokens_Scanner
{

	//------------------------------------------------------------------------------------ TOKEN SETS
	protected const BASIC_TYPES        = ['array', 'bool', 'callable', 'false', 'float', 'int', 'null', 'object', 'string', 'true', 'void'];
	protected const BASIC_VARIABLE     = [...self::BASIC_TYPES, T_VARIABLE];
	protected const CLASS_TOKENS       = [T_NAME_FULLY_QUALIFIED, T_NAME_QUALIFIED, T_STRING];
	protected const EXTENDS_IMPLEMENTS = [T_EXTENDS, T_IMPLEMENTS, '{', ';'];
	protected const FULL_TOKENS        = [T_CLASS, ...self::CLASS_TOKENS, T_STATIC, T_VARIABLE, '(', ')'];
	protected const MEMBER_TOKENS      = [T_CLASS, T_STRING, T_VARIABLE, '$', '(', ';'];
	protected const NAMESPACE_TOKENS   = [T_NAME_QUALIFIED, T_STRING];
	protected const RESERVED_CLASSES   = ['parent', 'self', 'static'];
	protected const VISIBILITY_TOKENS  = [T_CONST, T_FUNCTION, ...self::CLASS_TOKENS, T_VARIABLE];

	//------------------------------------------------------------------------------------ $attribute
	protected string $attribute = '';

	//---------------------------------------------------------------------- $attribute_bracket_depth
	protected int $attribute_bracket_depth = -1;

	//----------------------------------------------------------------------- $attribute_square_depth
	protected int $attribute_square_depth = 0;

	//-------------------------------------------------------------------------------- $bracket_depth
	protected int $bracket_depth = 0;

	//---------------------------------------------------------------------------------------- $class
	protected string $class = '';

	//--------------------------------------------------------------------------------- $class_depths
	/** @var int[] $class_depths */
	protected array $class_depths = [];

	//---------------------------------------------------------------------------------- $curly_depth
	protected int $curly_depth = 0;

	//------------------------------------------------------------------------------------ $namespace
	protected string $namespace = '';

	//------------------------------------------------------------------------------ $namespace_depth
	protected int $namespace_depth = 0;

	//-------------------------------------------------------------------------------- $namespace_use
	/** @var string[] */
	protected array $namespace_use = [];

	//------------------------------------------------------------------------------ $next_references
	public array $next_references = [];

	//----------------------------------------------------------------------------------- $references
	public array $references = [];

	//--------------------------------------------------------------------------------- $square_depth
	protected int $square_depth = 0;

	//-------------------------------------------------------------------------- appendNextReferences
	protected function appendNextReferences() : void
	{
		if (!$this->class) {
			$this->references = array_merge($this->references, $this->next_references);
		}
		else foreach ($this->next_references as $reference) {
			$reference[0]       = $this->class;
			$this->references[] = $reference;
		}
		$this->next_references = [];
	}

	//-------------------------------------------------------------------------------------- phpBlock
	/** @param $tokens int[]|string[]|string */
	protected function phpBlock(array &$tokens) : void
	{
		while ($token = next($tokens)) switch ($token[0]) {

			case '(':
				$this->bracket_depth ++;
				break;

			case ')':
				$this->bracket_depth --;
				break;

			case '{':
				$this->curly_depth ++;
				break;

			case '}':
				if ($this->curly_depth === end($this->class_depths)) {
					array_pop($this->class_depths);
					$this->class = '';
					break;
				}
				if ($this->curly_depth === $this->namespace_depth) {
					$this->namespace       = '';
					$this->namespace_depth = 0;
					$this->namespace_use   = [];
				}
				$this->curly_depth --;
				break;

			case '[':
				$this->square_depth ++;
				break;

			case ']':
				if ($this->square_depth === $this->attribute_square_depth) {
					$this->attribute               = '';
					$this->attribute_bracket_depth = -1;
					$this->attribute_square_depth  = 0;
				}
				$this->square_depth --;
				break;

			case T_ATTRIBUTE:
				$this->square_depth ++;
				$this->attribute_bracket_depth = $this->bracket_depth;
				$this->attribute_square_depth  = $this->square_depth;
				break;

			case T_CLASS:
				while (!in_array($token[0], [T_STRING, '{'], true)) $token = next($tokens);
				$this->class_depths[] = $this->curly_depth + 1;
				if ($token[0] === '{') {
					$this->curly_depth ++;
				}
				else {
					$this->class = $this->reference($token, 'declare-class') ?: $this->class;
				}
				if ($this->next_references) $this->appendNextReferences();
				break;

			case T_CLOSE_TAG:
				return;

			case T_EXTENDS:
			case T_IMPLEMENTS:
				if (!$this->class_depths || end($this->class_depths) !== ($this->bracket_depth + 1)) {
					break;
				}
				$type  = match($token[0]) { T_EXTENDS => 'extends', T_IMPLEMENTS => 'implements' };
				$token = next($tokens);
				while (!in_array($token[0], self::EXTENDS_IMPLEMENTS, true)) {
					if (in_array($token[0], self::CLASS_TOKENS, true)) {
						$this->reference($token, $type);
					}
					$token = next($tokens);
				}
				prev($tokens);
				break;

			case T_INTERFACE:
			case T_TRAIT:
				$type = match ($token[0]) {T_INTERFACE => 'interface', T_TRAIT => 'trait'};
				while ($token[0] !== T_STRING) $token = next($tokens);
				$this->class          = $this->reference($token, "declare-$type") ?: $this->class;
				$this->class_depths[] = $this->curly_depth + 1;
				if ($this->next_references) $this->appendNextReferences();
				break;

			case T_FUNCTION:
				if ($this->next_references) $this->appendNextReferences();
				while ($token !== '(') $token = next($tokens);
				while (($token = next($tokens)) !== ')') {
					if (
						in_array($token[0], self::CLASS_TOKENS, true)
						&& !in_array($token[1], self::BASIC_TYPES, true)
					) {
						$this->reference($token, 'argument');
						$token = next($tokens);
					}
					if ($token[0] === T_VARIABLE) {
						$token = next($tokens);
						while (is_array($token) || !str_contains(',)', $token)) $token = next($tokens);
						if ($token === ')') break;
					}
				}
				while (is_array($token) || !str_contains(':{;', $token)) $token = next($tokens);
				while (is_array($token) || !str_contains('{;', $token)) {
					if (
						in_array($token[0], self::CLASS_TOKENS, true)
						&& !in_array($token[1], self::BASIC_TYPES, true)
					) {
						$this->reference($token, 'return');
					}
					$token = next($tokens);
				}
				$this->curly_depth ++;
				return;

			case T_INSTANCEOF:
				while (!in_array($token[0], self::FULL_TOKENS, true)) $token = next($tokens);
				$this->reference($token, 'instance-of');
				break;

			case T_NAME_QUALIFIED:
			case T_NAME_FULLY_QUALIFIED:
			case T_STRING:
				if ($this->attribute_bracket_depth === $this->bracket_depth) {
					$this->attribute = $this->reference($token, 'attribute');
				}
				break;

			case T_NAMESPACE:
				while (!in_array($token[0], self::NAMESPACE_TOKENS, true)) $token = next($tokens);
				$this->reference([T_NAME_FULLY_QUALIFIED, $token[1], $token[2]], 'namespace');
				$this->namespace = $token[1];
				while (!is_string($token) || !str_contains(';{', $token)) $token = next($tokens);
				if ($token === '{') {
					$this->curly_depth ++;
					$this->namespace_depth = $this->curly_depth;
				}
				break;

			case T_NEW:
				while (!in_array($token[0], self::FULL_TOKENS, true)) $token = next($tokens);
				$this->reference($token, 'new');
				break;

			case T_PAAMAYIM_NEKUDOTAYIM:
				$back = 0;
				while (!in_array($token[0], self::MEMBER_TOKENS, true)) {
					$back ++;
					$token = next($tokens);
				}
				$type = ($token[0] === T_CLASS) ? 'class' : 'static';
				while ($back--) prev($tokens);
				$token = prev($tokens);
				while (!in_array($token[0], self::FULL_TOKENS, true)) $token = prev($tokens);
				if (!is_array($token) && in_array($token, ['}', ')'])) {
					// ignore dynamic class name before ::
					next($tokens);
					break;
				}
				if (in_array($token[1], self::RESERVED_CLASSES, true)) {
					$token[0] = T_NAME_FULLY_QUALIFIED;
					$token[1] = $this->class;
				}
				$this->reference($token, $type);
				while ($token[0] !== T_PAAMAYIM_NEKUDOTAYIM) $token = next($tokens);
				if ($type === 'class') while ($token[0] !== T_CLASS) $token = next($tokens);
				break;

			case T_USE:
				// class|trait T_USE
				if ($this->class_depths) {
					while (is_array($token) || !str_contains(';}', $token)) {
						while (!in_array($token[0], self::CLASS_TOKENS, true)) $token = next($tokens);
						$this->reference($token, 'use');
						$depth = 0;
						while ($depth || is_array($token) || !str_contains(',;}', $token)) {
							$token = next($tokens);
							switch ($token[0]) {
								case '{':
									$depth ++;
									break;
								case '}':
									$depth --;
									break;
								case T_PAAMAYIM_NEKUDOTAYIM:
									while (!in_array($token[0], self::CLASS_TOKENS, true)) $token = prev($tokens);
									$this->reference($token, 'static');
									while ($token[0] !== T_PAAMAYIM_NEKUDOTAYIM) $token = next($tokens);
									break;
							}
						}
					}
				}
				// namespace T_USE
				else {
					while ($token !== ';') {
						while (!in_array($token[0], self::CLASS_TOKENS, true)) $token = next($tokens);
						$use = ltrim($token[1], '\\');
						$this->reference([T_NAME_FULLY_QUALIFIED, $token[1], $token[2]], 'namespace-use');
						while ($token = next($tokens)) switch ($token[0]) {
							case T_AS:
								while ($token[0] !== T_STRING) $token = next($tokens);
								$this->namespace_use[$token[1]] = $use;
								while (is_array($token) || !str_contains(',;', $token)) $token = next($tokens);
								break 2;
							case ',':
							case ';':
								$this->namespace_use[substr($use, strrpos($use, '\\') + 1)] = $use;
								break 2;
						}
					}
				}
				break;

			case T_PRIVATE:
			case T_PROTECTED:
			case T_PUBLIC:
			case T_VAR:
				while (!in_array($token[0], self::VISIBILITY_TOKENS, true)) $token = next($tokens);
				if (in_array($token[0], self::BASIC_VARIABLE, true)) {
					$doc_comment = '';
					$line        = $token[2];
					$back        = 0;
					while (is_array($token) || !str_contains('{;', $token)) {
						if ($token[0] === T_DOC_COMMENT) {
							$doc_comment = $token[1] . $doc_comment;
							$line        = $token[2];
						}
						$token = prev($tokens);
						$back  ++;
					}
					$token = [null, null, $line];
					if (($start = strpos($doc_comment, '@var ')) !== false) {
						$start += 5;
						while (str_contains("\t\n *", $doc_comment[$start])) $start ++;
						$stop = $start;
						do {
							$stop ++;
							if (!str_contains("|&\t\n *", $doc_comment[$stop])) {
								continue;
							}
							$token[1] = rtrim(substr($doc_comment, $start, $stop - $start), '[]');
							$start    = $stop + 1;
							if ($token[1] === '') {
								continue;
							}
							if     (str_starts_with($token[1], '\\')) $token[0] = T_NAME_FULLY_QUALIFIED;
							elseif (str_contains($token[1], '\\'))    $token[0] = T_NAME_QUALIFIED;
							else                                      $token[0] = T_STRING;
							$this->reference($token, 'var');
						}
						while (!str_contains("\t\n *", $doc_comment[$stop]));
					}
					while ($back--) next($tokens);
				}
				elseif (in_array($token[0], self::CLASS_TOKENS, true)) {
					$this->reference($token, 'var');
				}
				else {
					prev($tokens);
				}
				break;
		}
	}

	//------------------------------------------------------------------------------------- reference
	protected function reference(array|string $token, string $type) : string
	{
		switch ($token[0]) {
			case T_NAME_FULLY_QUALIFIED:
				$name = ltrim($token[1], '\\');
				break;
			case T_NAME_QUALIFIED:
				$use  = $this->namespace_use[substr($token[1], 0, $slash = strpos($token[1], '\\'))] ?? '';
				$name = $use
					? ($use . substr($token[1], $slash))
					: ltrim($this->namespace . '\\' . $token[1], '\\');
				break;
			case T_STRING:
				if (in_array($token[1], static::BASIC_TYPES)) return '';
				$name = ($use = $this->namespace_use[$token[1]] ?? '')
					? $use
					: ltrim($this->namespace . '\\' . $token[1], '\\');
				break;
			default:
				return '';
		}
		$reference = [$this->class, $name, $type, $token[2]];
		if ($this->attribute !== '') {
			switch ($this->attribute) {
				case Extend::class:
					$reference[2] = '#Extend';
					break;
				case Implement::class:
					$reference[2] = '#Implement';
					break;
			}
		}
		if (!$this->class && ($this->attribute_square_depth || str_starts_with($type, 'declare-'))) {
			$this->next_references[] = $reference;
		}
		else {
			$this->references[] = $reference;
		}
		return $name;
	}

	//------------------------------------------------------------------------------------------ scan
	public function scan(array &$tokens) : void
	{
		$token = reset($tokens);
		while (current($tokens)) {
			while ($token && ($token[0] !== T_OPEN_TAG)) $token = next($tokens);
			$this->phpBlock($tokens);
		}
		if ($this->next_references) $this->appendNextReferences();
	}

}
