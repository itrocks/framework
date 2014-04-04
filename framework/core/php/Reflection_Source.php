<?php
namespace SAF\PHP;

/**
 * Reflection of PHP source code
 */
class Reflection_Source
{
	use Tokens_Parser;

	//----------------------------------------------------------------------------------------- const
	const CLASSES      = 1;
	const DEPENDENCIES = 2;
	const INSTANTIATES = 3;
	const NAMESPACES   = 4;
	const USES         = 5;

	//-------------------------------------------------------------------------------------- $classes
	/**
	 * @var Reflection_Class[] the key is the full name of each class
	 */
	private $classes;

	//-------------------------------------------------------------------------------------- $changed
	/**
	 * This is set to true when you call setSource(), in order to know that source has been changed
	 * and that you will probably need to write your PHP source file result.
	 * Used by Php_Compiler.
	 *
	 * @var boolean
	 */
	private $changed;

	//--------------------------------------------------------------------------------- $dependencies
	/**
	 * @var Dependency[]
	 */
	private $dependencies;

	//------------------------------------------------------------------------------------ $file_name
	/**
	 * @var string
	 */
	public $file_name;

	//----------------------------------------------------------------------------- $file_name_getter
	/**
	 * @var Class_File_Name_Getter
	 */
	private $file_name_getter;

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

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $file_name        string may be the name of a file
	 *                          or the PHP source code if beginning with '<?php'
	 * @param $file_name_getter Class_File_Name_Getter needed to get linked reflection objects
	 *                          without loading classes. If not, it will work, but with class
	 *                          loading.
	 */
	public function __construct(
		$file_name = null, Class_File_Name_Getter $file_name_getter = null
	) {
		if (isset($file_name)) {
			if (substr($file_name, 0, 5) === '<?php') {
				$this->source = $file_name;
				$this->changed = true;
			}
			else {
				$this->file_name = $file_name;
				$this->changed = false;
			}
		}
		$this->file_name_getter = $file_name_getter;
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
		if ($f_uses)         $this->use          = [];

		/** @var $class Reflection_Class */
		$class = new Reflection_Class($this);
		$class->name = '';
		$class->line = 1;
		// where did the last } come
		$last_stop = null;
		// the current namespace
		$this->namespace = '';
		// level for the T_USE clause : T_NAMESPACE, T_CLASS or T_FUNCTION (T_NULL if outside any level)
		$use_what = null;
		// what namespaces or class names does the current namespace use (key = val)
		$use = [];

		// scan tokens
		$this->getTokens();
		$tokens_count = count($this->tokens);
		for ($this->token_key = 0; $this->token_key < $tokens_count; $this->token_key ++) {
			$token = $this->tokens[$this->token_key];
			$token_id = $token[0];

			// stop
			if ($token_id === '}') {
				while (!is_array($token)) {
					$token = $this->tokens[++$this->token_key];
				}
				$last_stop = $token[2];
			}

			// namespace
			if ($token_id === T_NAMESPACE) {
				$use_what = T_NAMESPACE;
				$this->namespace = $this->scanClassName($this->token_key);
				$use = [];
				if ($f_namespaces) {
					$this->namespaces[$this->namespace] = $token[2];
				}
			}

			// use
			elseif ($token_id === T_USE) {

				// namespace use
				if ($use_what == T_NAMESPACE) {
					foreach ($this->scanClassNames($this->token_key) as $used => $line) {
						$use[$used] = $used;
						if ($f_uses) {
							$this->use[$used] = $line;
						}
					}
				}

				// class use (notice that this will never be called after T_DOUBLE_COLON)
				elseif ($use_what === T_CLASS) {
					if ($f_dependencies) {
						foreach ($this->scanTraitNames($this->token_key) as $trait_name => $line) {
							$trait_name = $this->fullClassName($trait_name);
							$dependency = new Dependency();
							$dependency->class_name      = $class->name;
							$dependency->dependency_name = $trait_name;
							$dependency->file_name       = $this->file_name;
							$dependency->line            = $line;
							$dependency->type            = T_USE;
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
				$class->stop = $last_stop;
				$class_name = $this->fullClassName($this->scanClassName(), false);
				$class = new Reflection_Class($this, $class_name);
				$class->line = $token[2];
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
					foreach ($this->scanClassNames() as $class_name => $line) {
						$class_name = $this->fullClassName($class_name);
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
						'%\*\s+@(param|return|var)\s+([\w\$\[\]\|\\\\]+)(?:\s+([\w\$\[\]\|\\\\]+))?%',
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
								$class_name = $this->fullClassName($class_name);
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
					$token = $this->tokens[$this->token_key - 1];
					$keyword = $this->tokens[++$this->token_key];
					$keyword = (is_array($keyword) && ($keyword[1] === 'class')) ? 'class' : 'static';
					if (($token[1][0] !== '$') && !in_array($token[1], ['self', 'static', '__CLASS__'])) {
						$class_name = $this->fullClassName($token[1]);
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
					$this->token_key++;
				}
			}

			// new
			elseif ($token_id === T_NEW) {
				if ($f_instantiates) {
					$class_name = $this->scanClassName();
					// $class_name is empty when 'new $class_name' (dynamic class name) : then ignore
					if ($class_name) {
						$class_name = $this->fullClassName($class_name);
						$dependency = new Dependency();
						$dependency->class_name      = $class->name;
						$dependency->dependency_name = $class_name;
						$dependency->file_name       = $this->file_name;
						$dependency->line            = $token[2];
						$dependency->type            = T_NEW;
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
		if (!isset($this->use))          $filters[] = self::USES;
		$this->get($filters);
		$result = get_object_vars($this);
		unset($result['lines']);
		unset($result['source']);
		unset($result['tokens']);
		return $result;
	}

	//-------------------------------------------------------------------------- getClassDependencies
	/**
	 * @param $class        Reflection_Class
	 * @param $instantiates boolean if true, searches for '::class' and 'new' too
	 * @return Dependency[]
	 */
	public function getClassDependencies(Reflection_Class $class, $instantiates = false)
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

	//-------------------------------------------------------------------------------------- getClass
	/**
	 * Gets a class from the source
	 *
	 * @param $class_name string
	 * @return Reflection_Class
	 */
	public function getClass($class_name)
	{
		return $this->getClasses()[$class_name];
	}

	//------------------------------------------------------------------------------------ getClasses
	/**
	 * Gets all declared classes full names
	 *
	 * @return Reflection_Class[]
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
			if (!isset($this->use))                           $filters[] = self::USES;
			$this->get($filters);
		}
		return $instantiates
			? arrayMergeRecursive($this->dependencies, $this->instantiates)
			: $this->dependencies;
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
			if (!isset($this->use))        $filters[] = self::USES;
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
				: file($this->source);
		}
		return $this->lines;
	}

	//------------------------------------------------------------------------------- getOutsideClass
	/**
	 * Uses the file name getter to get a Php_Class class object (and linked source) for a class name
	 * Use this to get a class from outside current source
	 */
	public function getOutsideClass($class_name)
	{
		$source = new Reflection_Source(
			$this->file_name_getter->getClassFilename($class_name),
			$this->file_name_getter
		);
		return $source->getClass($class_name);
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
	public function & getTokens()
	{
		if (!isset($this->tokens)) {
			$this->tokens = token_get_all($this->getSource());
		}
		return $this->tokens;
	}

	//------------------------------------------------------------------------------------ hasChanged
	/**
	 * @return boolean
	 */
	public function hasChanged()
	{
		return $this->changed;
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

	//------------------------------------------------------------------------------------------ free
	/**
	 * Reset object properties in order to free memory
	 *
	 * All features will work fine, you can call this to flush cash and free a maximum amount of
	 * memory but cache will be freed and next calls may be slower.
	 *
	 * Internals :
	 * Classes, dependencies, instantiates, namespaces and use are reset only if bigger than the
	 * $bigger_than parameter or if it is 0.
	 * Lines and tokens are always reset by this call.
	 * Source is reset only if source is linked to a file name.
	 *
	 * @param $bigger_than integer
	 */
	public function free($bigger_than = 1)
	{
		if (!$bigger_than || (count($this->classes)      > $bigger_than)) $this->classes      = null;
		if (!$bigger_than || (count($this->dependencies) > $bigger_than)) $this->dependencies = null;
		if (!$bigger_than || (count($this->instantiates) > $bigger_than)) $this->instantiates = null;
		if (!$bigger_than || (count($this->namespaces)   > $bigger_than)) $this->namespaces   = null;
		if (!$bigger_than || (count($this->use)          > $bigger_than)) $this->use          = null;

		if (isset($this->file_name)) {
			$this->source = null;
		}

		$this->lines  = null;
		$this->tokens = null;
	}

	//------------------------------------------------------------------------------------- setSource
	/**
	 * Sets the new source code
	 *
	 * Internals : Every properties but the file name are reset to zero by this change.
	 *
	 * @param $source string
	 * @return Reflection_Source
	 */
	public function setSource($source)
	{
		$this->free(0);
		$this->source = $source;
		$this->changed = true;
		return $this;
	}

}
