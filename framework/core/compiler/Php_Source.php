<?php
namespace SAF\Framework;

/**
 * A Php source file tokenizer
 */
class Php_Source
{

	//----------------------------------------------------------------------------------------- const
	const CLASSES      = 1;
	const DEPENDENCIES = 2;
	const INSTANTIATES = 3;
	const NAMESPACES   = 4;
	const USES         = 5;

	//-------------------------------------------------------------------------------------- $classes
	/**
	 * @var Dependency_Class[]
	 */
	private $classes;

	//--------------------------------------------------------------------------------- $dependencies
	/**
	 * @var Dependency[]
	 */
	private $dependencies;

	//------------------------------------------------------------------------------------ $file_name
	/**
	 * @var string
	 */
	private $file_name;

	//--------------------------------------------------------------------------------- $dependencies
	/**
	 * @var Dependency[]
	 */
	private $instantiates;

	//---------------------------------------------------------------------------------------- $lines
	/**
	 * @var string[]
	 */
	private $lines;

	/**
	 * @var integer[] key is the namespace, value is the line number where it is declared
	 */
	private $namespaces;

	//--------------------------------------------------------------------------------------- $source
	/**
	 * @var string
	 */
	private $source;

	//--------------------------------------------------------------------------------------- $tokens
	/**
	 * @var array
	 */
	private $tokens;

	//----------------------------------------------------------------------------------------- $uses
	/**
	 * @var integer[] key is the use (namespace or full class name), value is the line number
	 */
	private $uses;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $file_name string
	 */
	public function __construct($file_name)
	{
		$this->file_name = $file_name;
	}

	//--------------------------------------------------------------------------------- fullClassName
	/**
	 * Resolves the full class name for any class name in current source code context
	 *
	 * @param $class_name string the class name we want to get the full class name
	 * @param $namespace  string the current namespace
	 * @param $use        string[] the current use namespaces or class names
	 * @return string
	 */
	private function fullClassName($class_name, $namespace, $use = [])
	{
		// class name beginning with '\' : this is the full class name
		if ($class_name[0] === BS) {
			return substr($class_name, 1);
		}
		// class name containing '\' : search for namespace
		if ($length = strpos($class_name, BS)) {
			$search = BS . substr($class_name, 0, $length++);
			foreach ($use as $u) {
				$bu = BS . $u;
				if (substr($bu, -$length) === $search) {
					return ((strlen($bu) > $length) ? (substr($bu, 1, -$length) . BS) : '') . $class_name;
				}
			}
			return ($namespace ? ($namespace . BS) : '') . $class_name;
		}
		if ($use) {
			// class name without '\' : search for full class name
			$search = BS . $class_name;
			$length = strlen($search);
			foreach ($use as $u) {
				$bu = BS . $u;
				if(substr($bu, -$length) === $search) {
					return $u;
				}
			}
		}
		return ($namespace ? ($namespace . BS) : '') . $class_name;
	}

	//------------------------------------------------------------------------------------------- get
	/**
	 * @param $filter integer[] what to you want to get
	 */
	private function get($filter)
	{
		$filter = array_flip($filter);
		$f_classes      = isset($filter[self::CLASSES]);
		$f_dependencies = isset($filter[self::DEPENDENCIES]);
		$f_instantiates = isset($filter[self::INSTANTIATES]);
		$f_namespaces   = isset($filter[self::NAMESPACES]);
		$f_uses         = isset($filter[self::USES]);
		if ($f_classes)      $this->classes      = [];
		if ($f_dependencies) $this->dependencies = [];
		if ($f_instantiates) $this->instantiates = [];
		if ($f_namespaces)   $this->namespaces   = [];
		if ($f_uses)         $this->uses         = [];

		/** @var $class Dependency_Class */
		$class = null;
		// where did the last } come
		$last_stop = null;
		// the current namespace
		$namespace = '';
		// level for the T_USE clause : T_NAMESPACE, T_CLASS or T_FUNCTION (T_NULL if outside any level)
		$use_what = null;
		// what namespaces or class names does the current namespace use (key = val)
		$use = [];

		// scan tokens
		$tokens = $this->getTokens();
		for ($token = $tokens; $token; $token = next($tokens)) {
			$token_id = $token[0];

			// stop
			if ($token_id === '}') {
				while (!is_array($token)) {
					$token = next($tokens);
				}
				$last_stop = $token[2];
			}

			// namespace
			if ($token_id === T_NAMESPACE) {
				$use_what = T_NAMESPACE;
				$namespace = $this->parseClassName($tokens);
				$use = [];
				if ($f_namespaces) {
					$this->namespaces[$namespace] = $token[2];
				}
			}

			// use
			elseif ($token_id === T_USE) {

				// namespace use
				if ($use_what == T_NAMESPACE) {
					foreach ($this->parseClassNames($tokens) as $used => $line) {
						$use[$used] = $used;
						if ($f_uses) {
							$this->uses[$used] = $line;
						}
					}
				}

				// class use (notice that this will never be called after T_DOUBLE_COLON)
				elseif ($use_what === T_CLASS) {
					if ($f_dependencies) {
						foreach ($this->parseTraitNames($tokens) as $trait_name => $line) {
							$trait_name = $this->fullClassName($trait_name, $namespace, $use);
							$dependency = new Dependency();
							$dependency->class_name      = $class->name;
							$dependency->dependency_name = $trait_name;
							$dependency->file_name       = $this->file_name;
							$dependency->line            = $line;
							$dependency->type            = Dependency::T_USES;
							$this->dependencies[] = $dependency;
						}
					}
				}

				// function use
				elseif ($use_what == T_FUNCTION) {
					// ...
				}

			}

			// class, interface or trait
			elseif (in_array($token_id, [T_CLASS, T_INTERFACE, T_TRAIT])) {
				$use_what = T_CLASS;
				if (isset($class)) {
					$class->stop = $last_stop;
				}
				$class_name = $this->fullClassName($this->parseClassName($tokens), $namespace);
				$class = new Dependency_Class();
				$class->name = $class_name;
				$class->line = $token[2];
				$class->type = $token_id;
				if ($f_classes) {
					$this->classes[$class_name] = $class;
				}
			}
			elseif ($token_id === T_FUNCTION) {
				$use_what = T_FUNCTION;
			}

			// extends, implements
			elseif (in_array($token_id, [T_EXTENDS, T_IMPLEMENTS])) {
				if ($f_dependencies) {
					foreach ($this->parseClassNames($tokens) as $class_name => $line) {
						$class_name = $this->fullClassName($class_name, $namespace, $use);
						$dependency = new Dependency();
						$dependency->class_name      = $class->name;
						$dependency->dependency_name = $class_name;
						$dependency->file_name       = $this->file_name;
						$dependency->line            = $line;
						$dependency->type            = $this->nameOf($token_id);
						$this->dependencies[] = $dependency;
					}
				}
			}

			// doc comment
			elseif ($token_id === T_DOC_COMMENT) {
				if ($f_instantiates) {
					$doc_comment = $token[1];
					// 0 : everything until var name, 1 : type, 2 : Class_Name / $param, 3 : Class_Name
					preg_match_all(
						'%\*\s+@(param|return|var)\s+([\w\$\[\]\|]+)(?:\s+([\w\$\[\]\|]+))?%',
						$doc_comment,
						$matches,
						PREG_OFFSET_CAPTURE | PREG_SET_ORDER
					);

					foreach ($matches as $match) {
						list($class_name, $pos) = $match[2];
						if ($class_name[0] === '$') {
							list($class_name, $pos) = isset($match[3]) ? $match[3] : 'null';
						}
						foreach (explode('|', $class_name) as $class_name) {
							if (ctype_upper($class_name[0])) {
								$class_name = str_replace(['[', ']'], '', $class_name);
								$line = $token[2] + substr_count(substr($doc_comment, 0, $pos), LF);
								$type = $match[1][0];
								$class_name = $this->fullClassName($class_name, $namespace, $use);
								$dependency = new Dependency();
								$dependency->class_name      = $class->name;
								$dependency->dependency_name = $class_name;
								$dependency->file_name       = $this->file_name;
								$dependency->line            = $line;
								$dependency->type            = $type;
								$this->instantiates[] = $dependency;
							}
						}
					}
				}
			}

			// ::class
			elseif ($token_id === T_DOUBLE_COLON) {
				if ($f_instantiates) {
					$token = prev($tokens);
					next($tokens);
					$keyword = next($tokens);
					$keyword = ($keyword[1] === 'class') ? 'class' : 'static';
					if (!in_array($token[1], ['self', 'static', '__CLASS__'])) {
						$class_name = $this->fullClassName($token[1], $namespace, $use);
						$dependency = new Dependency();
						$dependency->class_name      = $class->name;
						$dependency->dependency_name = $class_name;
						$dependency->file_name       = $this->file_name;
						$dependency->line            = $token[2];
						$dependency->type            = $keyword;
						$this->instantiates[] = $dependency;
					}
				}
				else {
					next($tokens);
				}
			}

			// new
			elseif ($token_id === T_NEW) {
				if ($f_instantiates) {
					$class_name = $this->parseClassName($tokens);
					// $class_name is empty when 'new $class_name' (dynamic class name) : then ignore
					if ($class_name) {
						$class_name = $this->fullClassName($class_name, $namespace, $use);
						$dependency = new Dependency();
						$dependency->class_name      = $class->name;
						$dependency->dependency_name = $class_name;
						$dependency->file_name       = $this->file_name;
						$dependency->line            = $token[2];
						$dependency->type            = Dependency::T_NEW;
						$this->instantiates[] = $dependency;
					}
				}
			}

		}
		if (isset($class)) {
			$class->stop = $last_stop;
		}
	}

	//---------------------------------------------------------------------------------------- getAll
	/**
	 * Fill in all php source cache
	 *
	 * @return array
	 */
	public function getAll()
	{
		$filters = [];
		if (!isset($this->classes))      $filters[] = self::CLASSES;
		if (!isset($this->dependencies)) $filters[] = self::DEPENDENCIES;
		if (!isset($this->instantiates)) $filters[] = self::INSTANTIATES;
		if (!isset($this->namespaces))   $filters[] = self::NAMESPACES;
		if (!isset($this->uses))         $filters[] = self::USES;
		$this->get($filters);
		$result = get_object_vars($this);
		unset($result['lines']);
		unset($result['source']);
		unset($result['tokens']);
		return $result;
	}

	//-------------------------------------------------------------------------- getClassDependencies
	/**
	 * @param $class        Dependency_Class
	 * @param $instantiates boolean if true, searches for '::class' and 'new' too
	 * @return Dependency[]
	 */
	public function getClassDependencies(Dependency_Class $class, $instantiates = false)
	{
		$dependencies = [];
		foreach ($this->getDependencies($instantiates) as $dependency) {
			if (
				($dependency->line >= $class->line)
				&& ($dependency->line <= $class->stop)
				&& ($class->name !== $dependency->dependency_name)
			) {
				$dependencies[] = $dependency;
			}
		}
		return $dependencies;
	}

	//------------------------------------------------------------------------------------ getClasses
	/**
	 * Gets all declared classes full names
	 *
	 * @return Dependency_Class[]
	 */
	public function getClasses()
	{
		if (!isset($this->classes)) {
			$filters = [self::CLASSES];
			if (!isset($this->namespaces)) $filters[] = self::NAMESPACES;
			$this->get($filters);
		}
		return $this->classes;
	}

	//------------------------------------------------------------------------------- getDependencies
	/**
	 * Gets all dependencies full classes names
	 * - Looks for them using namespace 'use', classes 'extends', 'implements' and 'use'
	 *
	 * @param $instantiates boolean if true, searches for '::class' and 'new' too
	 * @return Dependency[]
	 */
	public function getDependencies($instantiates = false)
	{
		if (!isset($this->dependencies) || ($instantiates && !isset($this->instantiates))) {
			$filters = [];
			if ($instantiates && !isset($this->instantiates)) $filters[] = self::INSTANTIATES;
			if (!isset($this->dependencies))                  $filters[] = self::DEPENDENCIES;
			if (!isset($this->namespaces))                    $filters[] = self::NAMESPACES;
			if (!isset($this->uses))                          $filters[] = self::USES;
			$this->get($filters);
		}
		return $instantiates
			? arrayMergeRecursive($this->dependencies, $this->instantiates)
			: $this->dependencies;
	}

	//----------------------------------------------------------------------------------- getFileName
	/**
	 * @return string
	 */
	public function getFileName()
	{
		return $this->file_name;
	}

	//------------------------------------------------------------------------------- getInstantiates
	/**
	 * Gets all instantiates full classes names
	 * - Looks for them using '::class' and 'new'
	 *
	 * @return array key is the full class name
	 */
	public function getInstantiates()
	{
		if (!isset($this->instantiates)) {
			$filters = [self::INSTANTIATES];
			if (!isset($this->namespaces)) $filters[] = self::NAMESPACES;
			if (!isset($this->uses))       $filters[] = self::USES;
			$this->get($filters);
		}
		return $this->instantiates;
	}

	//-------------------------------------------------------------------------------------- getLines
	/**
	 * @return string[]
	 */
	public function getLines()
	{
		if (!isset($this->lines)) {
			$this->lines = isset($this->source)
				? explode(LF, $this->source)
				: $this->lines = file($this->source);
		}
		return $this->lines;
	}

	//------------------------------------------------------------------------------------- getSource
	/**
	 * @return string
	 */
	public function getSource()
	{
		if (!isset($this->source)) {
			$this->source = isset($this->lines)
				? join(LF, $this->lines)
				: file_get_contents($this->file_name);
		}
		return $this->source;
	}

	//------------------------------------------------------------------------------------- getTokens
	/**
	 * @return array
	 */
	public function getTokens()
	{
		if (!isset($this->tokens)) {
			$this->tokens = token_get_all($this->getSource());
		}
		return $this->tokens;
	}

	//---------------------------------------------------------------------------------------- nameOf
	/**
	 * Returns the name that corresponds to a token id, lowercase and without the 'T_' prefix
	 *
	 * @param $token_id integer
	 * @return string
	 */
	private function nameOf($token_id)
	{
		return strtolower(substr(token_name($token_id), 2));
	}

	//-------------------------------------------------------------------------------- parseClassName
	/**
	 * Parses a class name
	 *
	 * @param $tokens array the current selected token must be before the first T_STRING or T_WHITESPACE
	 * @return string
	 */
	private function parseClassName(&$tokens)
	{
		$class_name = '';
		do {
			$token = next($tokens);
		} while ($token[0] === T_WHITESPACE);
		while (in_array($token[0], [T_NS_SEPARATOR, T_STRING])) {
			$class_name .= $token[1];
			$token = next($tokens);
		}
		return $class_name;
	}

	//------------------------------------------------------------------------------- parseClassNames
	/**
	 * Parses commas separated class names
	 *
	 * @param $tokens array the current selected token must be before the first T_STRING
	 * @return string[]
	 */
	private function parseClassNames(&$tokens)
	{
		$classe_names = [];
		$line = 0;
		$used = '';
		do {
			$token = next($tokens);
			if (in_array($token[0], [T_NS_SEPARATOR, T_STRING])) {
				$line = $token[2];
				$used .= $token[1];
			}
			elseif ($token === ',') {
				$classe_names[$used] = $line;
				$used = '';
			}
		} while (($token === ',') || in_array($token[0], [T_NS_SEPARATOR, T_STRING, T_WHITESPACE]));
		if ($used) {
			$classe_names[$used] = $line;
		}
		return $classe_names;
	}

	//------------------------------------------------------------------------------- parseTraitNames
	/**
	 * Parse commas separated trait names. Ignore { } traits details
	 *
	 * @param $tokens array the current selected token must be before the first T_STRING
	 * @return integer[] key is the trait name, value is the line number it was declared
	 */
	private function parseTraitNames(&$tokens)
	{
		$trait_names = [];
		$trait_name = '';
		$depth = 0;
		$line = 0;
		do {
			$token = next($tokens);
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
				}
				elseif (($token_id == T_STRING) && !$depth) {
					$trait_name .= $token[1];
					$line = $token[2];
				}
			}
		} while ($token !== ';');
		if ($trait_name) {
			$trait_names[$trait_name] = $line;
		}
		return $trait_names;
	}

	//----------------------------------------------------------------------------------------- reset
	/**
	 * Reset object properties in order to free memory
	 *
	 * Classes, dependencies, instantiates, namespaces and uses are reset only if bigger than the
	 * $bigger_than parameter or if it is 0.
	 *
	 * Lines, source and tokens are always reset by this call.
	 *
	 * @param $bigger_than integer
	 */
	public function reset($bigger_than = 1)
	{
		if (!$bigger_than || (count($this->classes)      > $bigger_than)) $this->classes      = null;
		if (!$bigger_than || (count($this->dependencies) > $bigger_than)) $this->dependencies = null;
		if (!$bigger_than || (count($this->instantiates) > $bigger_than)) $this->instantiates = null;
		if (!$bigger_than || (count($this->namespaces)   > $bigger_than)) $this->namespaces   = null;
		if (!$bigger_than || (count($this->uses)         > $bigger_than)) $this->uses         = null;
		$this->lines  = null;
		$this->source = null;
		$this->tokens = null;
	}

	//------------------------------------------------------------------------------------- setSource
	/**
	 * Sets the new source code
	 * Every properties but the file name are reset to zero by this change.
	 *
	 * @param $source string
	 */
	public function setSource($source)
	{
		$this->reset(0);
		$this->source = $source;
	}

}
