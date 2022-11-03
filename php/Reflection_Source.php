<?php
namespace ITRocks\Framework\PHP;

use ITRocks\Framework\AOP\Include_Filter;
use ITRocks\Framework\Builder\Class_Builder;
use ITRocks\Framework\PHP\Dependency\Declaration;
use ITRocks\Framework\Reflection\Annotation\Template\List_Annotation;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Tools\Namespaces;
use ITRocks\Framework\Tools\Paths;

/**
 * Reflection of PHP source code
 */
class Reflection_Source
{
	use Tokens_Parser;

	//----------------------------------------------------------------------- get() filters constants
	const CLASSES      = 1;
	const DEPENDENCIES = 2;
	const INSTANTIATES = 3;
	const NAMESPACES   = 4;
	const REQUIRES     = 5;
	const USES         = 6;

	//----------------------------------------------------------------------- $accept_compiled_source
	/**
	 * If true, getSource() will be able to load source from its compiled version instead of original
	 *
	 * @var boolean
	 */
	private bool $accept_compiled_source;

	//---------------------------------------------------------------------------------------- $cache
	/**
	 * Reflection_Source cache : all files are kept here with two indices : file name and class name
	 * This allow to always have only one version of a Source, at any time (needed for PHP Compiler)
	 *
	 * The cache is filled in when you use __construct(), ofClass() or ofFile().
	 * But the cache is not used into __construct(), so you may have duplicates if you do not use of()
	 *
	 * @var Reflection_Source[] key is file_name and class_name
	 */
	private static array $cache = [];

	//-------------------------------------------------------------------------------------- $changed
	/**
	 * This is set to true when you call setSource(), in order to know that source has been changed
	 * and that you will probably need to write your PHP source file result.
	 * Used by Compiler.
	 *
	 * @var boolean
	 */
	private bool $changed = false;

	//---------------------------------------------------------------------------------- $class_cache
	/**
	 * @var Reflection_Class[]
	 */
	public static array $class_cache = [];

	//-------------------------------------------------------------------------------------- $classes
	/**
	 * @var Reflection_Class[] the key is the full name of each class
	 */
	private array $classes;

	//--------------------------------------------------------------------------------- $dependencies
	/**
	 * @var Dependency[]
	 */
	private array $dependencies;

	//------------------------------------------------------------------------------------ $file_name
	/**
	 * @var ?string
	 */
	public ?string $file_name = null;

	//--------------------------------------------------------------------------------- $instantiates
	/**
	 * @var Dependency[]
	 */
	private array $instantiates;

	//------------------------------------------------------------------------------------- $internal
	/**
	 * @var boolean
	 */
	private bool $internal;

	//---------------------------------------------------------------------------------------- $lines
	/**
	 * @var string[]
	 */
	private array $lines;

	//----------------------------------------------------------------------------------- $namespaces
	/**
	 * @var integer[] key is the namespace, value is the line number where it is declared
	 */
	private array $namespaces;

	//------------------------------------------------------------------------------------- $requires
	/**
	 * @var integer[] key is a string PHP file path, value is the line number where it is declared
	 */
	public array $requires;

	//--------------------------------------------------------------------------------------- $source
	/**
	 * @var string
	 */
	private string $source;

	//----------------------------------------------------------- $token_id_to_dependency_declaration
	/**
	 * @var string[] key is the declaration token id, value is the Dependency::$declaration value
	 */
	private static array $token_id_to_dependency_declaration = [
		T_CLASS     => Dependency::T_CLASS_DECLARATION,
		T_INTERFACE => Dependency::T_INTERFACE_DECLARATION,
		T_TRAIT     => Dependency::T_TRAIT_DECLARATION
	];

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $file_name  string|null may be the name of a file
	 *                    or the PHP source code if beginning with '<?php'
	 * @param $class_name string|null If file name can be null, $class_name will force initialisation
	 *                    of classes as a Reflection_Class object for $class_name
	 */
	public function __construct(string $file_name = null, string $class_name = null)
	{
		$this->accept_compiled_source = !empty($file_name);
		if (isset($file_name)) {
			if (str_starts_with($file_name, '<?php')) {
				$this->source  = $file_name;
				$this->changed = true;
			}
			else {
				$this->file_name = $file_name;
				$this->changed   = false;
			}
		}
		if ($this->internal = (!$file_name && $class_name)) {
			$class = static::$class_cache[$class_name] ?? new Reflection_Class($this, $class_name);
			$this->classes = [$class_name => $class];
		}
		$source_class_name = $this->getFirstClassName();
		if ($class_name && ($class_name !== $source_class_name)) {
			trigger_error(
				"Build Reflection_Source($file_name, $class_name)"
				. SP . "has non-matching class $source_class_name into source",
				E_USER_ERROR
			);
		}
		if ($source_class_name) {
			$class_name = $source_class_name;
		}
		if ($class_name && !isset(self::$cache[$class_name])) self::$cache[$class_name] = $this;
		if ($file_name  && !isset(self::$cache[$file_name ])) self::$cache[$file_name ] = $this;
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
	public function free(int $bigger_than = 1)
	{
		if (isset($this->classes)) {
			foreach ($this->classes as $class) {
				$class->free();
			}
		}

		if (isset($this->classes) && (!$bigger_than || (count($this->classes) > $bigger_than))) {
			unset($this->classes);
		}
		if (isset($this->dependencies) && (!$bigger_than || (count($this->dependencies) > $bigger_than))) {
			unset($this->dependencies);
		}
		if (isset($this->instantiates) && (!$bigger_than || (count($this->instantiates) > $bigger_than))) {
			unset($this->instantiates);
		}
		if (isset($this->namespaces) && (!$bigger_than || (count($this->namespaces) > $bigger_than))) {
			unset($this->namespaces);
		}
		if (isset($this->requires) && (!$bigger_than || (count($this->requires) > $bigger_than))) {
			unset($this->requires);
		}
		if (isset($this->use) && (!$bigger_than || (count($this->use) > $bigger_than))) {
			unset($this->use);
		}
		if (isset($this->file_name) && is_file($this->file_name) && !$this->changed) {
			unset($this->source);
		}

		unset($this->lines);
		$this->tokens = [];
	}

	//------------------------------------------------------------------------------------------- get
	/**
	 * @param $filter integer[] what to you want to get
	 */
	private function get(array $filter)
	{
		$filter = array_flip($filter);
		$f_classes      = isset($filter[self::CLASSES]);
		$f_dependencies = isset($filter[self::DEPENDENCIES]);
		$f_instantiates = isset($filter[self::INSTANTIATES]);
		$f_namespaces   = isset($filter[self::NAMESPACES]);
		$f_requires     = isset($filter[self::REQUIRES]);
		$f_uses         = isset($filter[self::USES]);
		if ($f_classes)      $this->classes      = [];
		if ($f_dependencies) $this->dependencies = [];
		if ($f_instantiates) $this->instantiates = [];
		if ($f_namespaces)   $this->namespaces   = [];
		if ($f_requires)     $this->requires     = [];
		if ($f_uses)         $this->use          = [];

		// the current namespace
		$this->namespace = '';

		if ($this->internal) {
			return;
		}

		// a blank class to have a valid scan beginning, but has no any other use
		$class = new Reflection_Class($this, '');
		// how deep is the current class
		$class_depth = -1;
		// how deep we are in {
		$depth = 0;
		// level for the T_USE clause : T_NAMESPACE, T_CLASS or T_FUNCTION (T_NULL if outside any level)
		$use_what = null;

		// scan tokens
		$this->getTokens();
		$tokens_count = count($this->tokens);
		for ($this->token_key = 0; $this->token_key < $tokens_count; $this->token_key ++) {
			$token    = $this->tokens[$this->token_key];
			$token_id = $token[0];
			if (isset($class_end) && is_array($token)) {
				$class->stop = $token[2] - 1;
				unset($class_end);
			}

			// stop
			if ($token_id === '{') {
				$depth ++;
				$token = $this->tokens[++$this->token_key];
			}
			if ($token_id === '}') {
				$depth --;
				if ($depth === $class_depth) {
					$class_end = true;
				}
				while ((($this->token_key + 1) < $tokens_count) && !is_array($token)) {
					$token = $this->tokens[++$this->token_key];
				}
			}

			// namespace
			if ($token_id === T_NAMESPACE) {
				$use_what = T_NAMESPACE;
				$this->namespace = $this->scanClassName();
				if ($f_namespaces) {
					$this->namespaces[$this->namespace] = $token[2];
				}
			}

			// require_once
			if (in_array($token_id, [T_INCLUDE, T_INCLUDE_ONCE, T_REQUIRE, T_REQUIRE_ONCE])) {
				$eval = str_replace(
					['__DIR__', '__FILE__'],
					[Q . lLastParse($this->file_name, SL) . Q, Q . $this->file_name . Q],
					$this->scanRequireFilePath()
				);
				if (str_contains($eval, '$')) {
					$require_name = $eval;
				}
				elseif (!str_contains($eval, '::') && !str_contains($eval, '->')) {
					/** @var $require_name string */
					eval('$require_name = ' . $eval . ';');
					if (!isset($require_name)) {
						trigger_error(
							'Bad $require_name ' . $eval . ' into ' . $this->file_name . ' line ' . $token[2],
							E_USER_ERROR
						);
					}
					$guard = 10;
					while (str_contains($require_name, '/../') && $guard--) {
						$require_name = preg_replace('%\\w+/../%', '', $require_name);
					}
					if (!$guard) {
						trigger_error(
							'Guard woke up on ' . $require_name
							. ' into ' . $this->file_name . ' line ' . $token[2]
						);
					}
				}
				else {
					unset($require_name);
				}
				if (isset($require_name)) {
					if ($class->name) {
						$class->requires[$require_name] = $token[2];
					}
					else {
						$this->requires[$require_name] = $token[2];
					}
				}
			}

			// use
			elseif ($token_id === T_USE) {

				// namespace use
				if ($use_what === T_NAMESPACE) {
					foreach ($this->scanClassNames() as $used => $line) {
						if ($used[0] === BS) {
							trigger_error(
								'Coding standards : use ' . $used . ' do not need to begin with a back-slash'
								. ' into ' . $this->file_name . '. This may cause drawbacks to the framework.',
								E_USER_WARNING
							);
							$used = substr($used, 1);
						}
						if ($f_uses) {
							$this->use[$used] = $line;
						}
						$dependency = new Dependency();
						$dependency->dependency_name = $used;
						$dependency->file_name       = $this->file_name;
						$dependency->line            = $line;
						$dependency->type            = Dependency::T_NAMESPACE_USE;
						$this->dependencies[] = $dependency;
						$missing_class_name[] = $dependency;
					}
				}

				// class use (notice that this will never be called after T_DOUBLE_COLON)
				elseif ($use_what === T_CLASS) {
					if ($f_dependencies) {
						foreach ($this->scanTraitNames() as $trait_name => $line) {
							$trait_name = $this->fullClassName($trait_name);
							$dependency = new Dependency();
							$dependency->class_name      = $class->name;
							$dependency->dependency_name = $trait_name;
							$dependency->file_name       = $this->file_name;
							$dependency->line            = $line;
							$dependency->type            = Dependency::T_USE;
							$this->dependencies[] = $dependency;
						}
					}
				}

				/*
				// function use
				elseif ($use_what === T_FUNCTION) {
					// ...
				}
				*/

			}

			// class, interface or trait
			elseif (
				in_array($token_id, [T_CLASS, T_INTERFACE, T_TRAIT])
				&& (($this->tokens[$this->token_key - 2][0] ?? null) !== T_NEW)
			) {
				$use_what   = T_CLASS;
				$class_name = $this->fullClassName($this->scanClassName(), false);
				if (substr($class_name, -1) === BS) {
					trigger_error(
						'bad class name ' . $class_name . SP . print_r($token, true)
						. ' in file ' . $this->file_name . ' line ' . $token[2],
						E_USER_ERROR
					);
				}
				$class = static::$class_cache[$class_name] ?? new Reflection_Class($this, $class_name);
				$class->line = $token[2];
				$class->type = $token_id;
				$class_depth = $depth;
				if ($f_classes) {
					$this->classes[$class_name] = $class;
				}
				$dependency = new Dependency();
				$dependency->class_name      = $class->name;
				$dependency->declaration     = self::$token_id_to_dependency_declaration[$token_id];
				$dependency->dependency_name = $class->name;
				$dependency->file_name       = $this->file_name;
				$dependency->line            = $token[2];
				$dependency->type            = Dependency::T_DECLARATION;
				$this->dependencies[] = $dependency;
				if (isset($missing_class_name)) {
					foreach ($missing_class_name as $dependency) {
						$dependency->class_name = $class->name;
					}
					unset($missing_class_name);
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

					// dependencies @param, @return, @set, @var
					// 0 : everything until var name, 1 : type, 2 : Class_Name / $param, 3 : Class_Name
					preg_match_all(
						'%\*\s+@(param|return|set|var)\s+(?:@local\s+)?([\w\$\[\]|\\\\]+)(?:\s+\??([\w\$\[\]|\\\\]+))?%',
						$doc_comment,
						$matches,
						PREG_OFFSET_CAPTURE | PREG_SET_ORDER
					);

					foreach ($matches as $match) {
						[$class_names, $pos] = $match[2];
						$class_names = str_replace('$this', 'static', $class_names);
						if ($class_names[0] === '$') {
							[$class_names, $pos] = $match[3] ?? ['', $match[2][1]];
						}
						$line = $token[2] + substr_count(substr($doc_comment, 0, $pos), LF);
						if (strlen($class_names)) {
							foreach (explode('|', $class_names) as $class_name) {
								if (ctype_upper($class_name[0])) {
									$class_name                  = str_replace(['[', ']'], '', $class_name);
									$type                        = $match[1][0];
									$class_name                  = $this->fullClassName($class_name);
									$dependency                  = new Dependency();
									$dependency->class_name      = $class->name;
									$dependency->dependency_name = $class_name;
									$dependency->file_name       = $this->file_name;
									$dependency->line            = $line;
									$dependency->type            = $type;
									if ($use_what === T_CLASS) {
										$dependency->declaration = Dependency::T_PROPERTY_DECLARATION;
									}
									$this->instantiates[] = $dependency;
									if (!$class->name) {
										$missing_class_name[] = $dependency;
									}
									if ($type === Dependency::T_SET) {
										$dependency                  = clone $dependency;
										$dependency->dependency_name = strtolower(
											Namespaces::shortClassName($class_name)
										);
										$dependency->type     = Dependency::T_STORE;
										$this->dependencies[] = $dependency;
										if (!$class->name) {
											$missing_class_name[] = $dependency;
										}
									}
								}
							}
						}
						else {
							trigger_error(
								'Bad annotation ' . substr($match[0][0], 2)
								. ' into file ' . $this->file_name . ' at line ' . $line
								. ' : var type / class is needed',
								E_USER_WARNING
							);
						}
					}

					// dependencies @bridge_feature / @compatibility
					preg_match_all(
						'%\*\s+@(bridge_feature|compatibility)\s+([A-Z].*)%',
						$doc_comment,
						$matches,
						PREG_OFFSET_CAPTURE | PREG_SET_ORDER
					);
					foreach ($matches as $match) {
						[$compatibility_value, $pos] = $match[2];
						$type        = $match[1][0];
						$declaration = ($type === Dependency::T_BRIDGE_FEATURE ? Declaration::INSTALLABLE : '');
						$line        = $token[2] + substr_count(substr($doc_comment, 0, $pos), LF);
						$annotation  = new List_Annotation($compatibility_value);
						foreach ($annotation->values() as $compatibility_class_name) {
							$compatibility_class_name = $this->fullClassName($compatibility_class_name);
							$dependency = new Dependency();
							$dependency->class_name      = $class->name;
							$dependency->declaration     = $declaration;
							$dependency->dependency_name = $compatibility_class_name;
							$dependency->file_name       = $this->file_name;
							$dependency->line            = $line;
							$dependency->type            = $type;
							$this->dependencies[] = $dependency;
							if (!$class->name) {
								$missing_class_name[] = $dependency;
							}
						}
					}

					// dependency @feature
					preg_match_all(
						'%\*\s+@(assigned_feature|built_in_feature|feature|installable_feature)\s+([A-Z@].*)%',
						$doc_comment,
						$matches,
						PREG_OFFSET_CAPTURE | PREG_SET_ORDER
					);
					foreach ($matches as $match) {
						$type          = $match[1][0];
						[$title, $pos] = $match[2];
						$line          = $token[2] + substr_count(substr($doc_comment, 0, $pos), LF);
						$declaration   = str_replace('_', '-', lParse($type, '_feature', 1, false));
						if (in_array(substr($title, 0, 10), ['@built_in ', '@built-in '])) {
							$title       = trim(substr($title, 10));
							$declaration = Declaration::BUILT_IN;
						}
						elseif (substr($title, 0, 1) === AT) {
							break;
						}
						if (in_array($declaration, ['built_in', 'built-in'])) {
							$declaration = Declaration::BUILT_IN;
						}
						elseif ($declaration === Declaration::ASSIGNED) {
							$title = $this->fullClassName($title);
						}
						elseif (!$declaration) {
							if (str_contains($title, SP)) {
								$declaration = Declaration::INSTALLABLE;
							}
							else {
								// may be a class name, if in use. Else it is just a title in one word
								$namespace  = $this->namespace;
								$title_file = strtolower(str_replace(BS, SL, $this->namespace)) . SL . $title;
								if (!is_file($title_file . '.php')) {
									$this->namespace = '';
								}
								$title           = $this->fullClassName($title);
								$this->namespace = $namespace;
								$declaration     = str_contains($title, BS)
									? Declaration::ASSIGNED
									: Declaration::INSTALLABLE;
							}
						}
						$dependency = new Dependency();
						$dependency->class_name      = $class->name;
						$dependency->declaration     = ($declaration ?: Declaration::INSTALLABLE);
						$dependency->dependency_name = $title;
						$dependency->file_name       = $this->file_name;
						$dependency->line            = $line;
						$dependency->type            = Dependency::T_FEATURE;
						$this->dependencies[] = $dependency;
						if (!$class->name) {
							$missing_class_name[] = $dependency;
						}
					}
				}
			}

			// ::class
			elseif ($token_id === T_DOUBLE_COLON) {
				if ($f_instantiates) {
					$token = $this->tokens[$this->token_key - 1];
					if ($token[1][0] !== '$') {
						$tk = $this->token_key - 1;
						$class_name = '';
						do {
							$class_name = $this->tokens[$tk][1] . $class_name;
							$tk --;
						}
						while (in_array($this->tokens[$tk][0], CLASS_NAME_TOKENS));
						$class_name = in_array($token[1], ['__CLASS__', 'self', 'static'])
							? $class->name
							: $this->fullClassName($class_name);
						$type = $this->tokens[++$this->token_key];
						$type = (is_array($type) && ($type[1] === 'class'))
							? Dependency::T_CLASS
							: Dependency::T_STATIC;
						$dependency = new Dependency();
						$dependency->class_name      = $class->name;
						$dependency->dependency_name = $class_name;
						$dependency->file_name       = $this->file_name;
						$dependency->line            = $token[2];
						$dependency->type            = $type;
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
						$dependency->type            = Dependency::T_NEW;
						$this->instantiates[] = $dependency;
					}
				}
			}

		}
	}

	//---------------------------------------------------------------------------------------- getAll
	/**
	 * Fill in all php source cache
	 *
	 * @return array
	 */
	public function getAll() : array
	{
		$filters = [self::DEPENDENCIES];
		if (!isset($this->classes))      $filters[] = self::CLASSES;
		if (!isset($this->instantiates)) $filters[] = self::INSTANTIATES;
		$this->getFilters($filters);
		$result = get_object_vars($this);
		unset($result['lines']);
		unset($result['source']);
		unset($result['tokens']);
		return $result;
	}

	//-------------------------------------------------------------------------------------- getClass
	/**
	 * Gets a class from the source
	 *
	 * @param $class_name string
	 * @return Reflection_Class
	 */
	public function getClass(string $class_name) : Reflection_Class
	{
		$classes = $this->getClasses();
		return $classes[$class_name]
			?? static::$class_cache[$class_name]
			?? new Reflection_Class($this, $class_name);
	}

	//-------------------------------------------------------------------------- getClassDependencies
	/**
	 * @param $class        Reflection_Class
	 * @param $instantiates boolean if true, searches for '::class' and 'new' too
	 * @return Dependency[]
	 */
	public function getClassDependencies(Reflection_Class $class, bool $instantiates = false) : array
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
	 * Gets all declared classes
	 *
	 * @return Reflection_Class[]
	 */
	public function getClasses() : array
	{
		if (!isset($this->classes)) {
			$this->getFilters([self::CLASSES, self::DEPENDENCIES]);
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
	public function getDependencies(bool $instantiates = false) : array
	{
		if (!isset($this->dependencies) || ($instantiates && !isset($this->instantiates))) {
			$filters = [self::DEPENDENCIES];
			if ($instantiates && !isset($this->instantiates)) $filters[] = self::INSTANTIATES;
			$this->getFilters($filters);
		}
		return $instantiates
			? arrayMergeRecursive($this->dependencies, $this->instantiates)
			: $this->dependencies;
	}

	//------------------------------------------------------------------------------------ getFilters
	/**
	 * @param $filters array
	 */
	protected function getFilters(array $filters)
	{
		if (!isset($this->namespaces)) $filters[] = self::NAMESPACES;
		if (!isset($this->requires))   $filters[] = self::REQUIRES;
		if (!isset($this->use))        $filters[] = self::USES;
		$this->get($filters);
	}

	//--------------------------------------------------------------------------------- getFirstClass
	/**
	 * Gets the first class into source (null if none)
	 *
	 * @return ?Reflection_Class
	 */
	public function getFirstClass() : ?Reflection_Class
	{
		$classes = $this->getClasses();
		return $classes ? reset($classes) : null;
	}

	//----------------------------------------------------------------------------- getFirstClassName
	/**
	 * Gets the name of the first class into source (null if none)
	 *
	 * @return ?string
	 */
	public function getFirstClassName() : ?string
	{
		return $this->getFirstClass()?->name;
	}

	//------------------------------------------------------------------------------- getInstantiates
	/**
	 * Gets all instantiates full classes names
	 * - Looks for them using '::class' and 'new'
	 *
	 * @return array key is the full class name
	 */
	public function getInstantiates() : array
	{
		if (!isset($this->instantiates)) {
			$this->getFilters([self::DEPENDENCIES, self::INSTANTIATES]);
		}
		return $this->instantiates;
	}

	//-------------------------------------------------------------------------------------- getLines
	/**
	 * @return string[]
	 */
	public function getLines() : array
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
	 * Uses the file name getter to get a Reflection_Class class object (and linked source)
	 * for a class name.
	 * Use this to get a class from outside current source.
	 *
	 * @param $class_name string
	 * @return Reflection_Class
	 */
	public function getOutsideClass(string $class_name) : Reflection_Class
	{
		if (substr($class_name, 0, 1) === BS) {
			$class_name = substr($class_name, 1);
		}
		if (isset(self::$cache[$class_name])) {
			$source = self::$cache[$class_name];
		}
		else {
			$filename = Names::classToFilePath($class_name);
			// consider vendor classes like internal classes : we don't work with their sources
			$source = str_starts_with($filename, 'vendor/')
				? (self::$cache[$class_name] ?? new Reflection_Source(null, $class_name))
				: Reflection_Source::ofFile($filename, $class_name);
			self::$cache[$class_name] = $source;
			if (!empty($filename)) {
				self::$cache[$filename] = $source;
			}
		}
		return $source->getClass($class_name);
	}

	//----------------------------------------------------------------------------------- getRequires
	/**
	 * @return integer[] the key is the required file path, the value is the line number
	 */
	public function getRequires() : array
	{
		if (!isset($this->requires)) {
			$this->getAll();
		}
		return $this->requires;
	}

	//------------------------------------------------------------------------------------- getSource
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return string
	 */
	public function getSource() : string
	{
		if (!isset($this->source)) {
			if ($this->file_name) {
				/** @noinspection PhpUnhandledExceptionInspection file_exists */
				$file_name = $this->accept_compiled_source
					? Include_Filter::file($this->file_name)
					: $this->file_name;
				$this->source = isset($this->lines)
					? join(LF, $this->lines)
					: ($file_name ? file_get_contents($file_name) : '');
				if (!$this->source && (!$file_name || !file_exists($file_name))) {
					echo PRE . print_r(get_declared_classes(), true) . _PRE;
					trigger_error('Could not get source', E_USER_WARNING);
				}
			}
			else {
				$this->source = '';
			}
		}
		return $this->source;
	}

	//------------------------------------------------------------------------------------- getTokens
	/**
	 * @return array
	 */
	public function & getTokens() : array
	{
		if (!$this->tokens) {
			$this->tokens = token_get_all($this->getSource());
		}
		return $this->tokens;
	}

	//------------------------------------------------------------------------------------ hasChanged
	/**
	 * @return boolean
	 */
	public function hasChanged() : bool
	{
		return $this->changed;
	}

	//------------------------------------------------------------------------------------ isInternal
	/**
	 * @return boolean
	 */
	public function isInternal() : bool
	{
		return $this->internal;
	}

	//---------------------------------------------------------------------------------------- nameOf
	/**
	 * Returns the name that corresponds to a token id, lowercase and without the 'T_' prefix
	 *
	 * @param $token_id integer
	 * @return string
	 */
	private function nameOf(int $token_id) : string
	{
		return strtolower(substr(token_name($token_id), 2));
	}

	//--------------------------------------------------------------------------------------- ofClass
	/**
	 * @param $class_name string
	 * @return Reflection_Source
	 */
	public static function ofClass(string $class_name) : Reflection_Source
	{
		if (isset(self::$cache[$class_name])) {
			return self::$cache[$class_name];
		}
		$file_name = Class_Builder::isBuilt($class_name)
			? Compiler::classToCacheFilePath($class_name)
			: Names::classToFilePath($class_name);
		if (!file_exists($file_name)) {
			$file_name = null;
		}
		return ($file_name && isset(self::$cache[$file_name]))
			? self::$cache[$file_name]
			: new Reflection_Source($file_name, $class_name);
	}

	//---------------------------------------------------------------------------------------- ofFile
	/**
	 * @param $file_name  string
	 * @param $class_name string|null
	 * @return Reflection_Source
	 */
	public static function ofFile(string $file_name, string $class_name = null) : Reflection_Source
	{
		if (isset(self::$cache[$file_name])) {
			$result = self::$cache[$file_name];
		}
		elseif ($class_name) {
			$result = self::$cache[$class_name] ?? new Reflection_Source($file_name, $class_name);
			self::$cache[$file_name] = $result;
		}
		else {
			$result = new Reflection_Source($file_name);
		}
		return $result;
	}

	//-------------------------------------------------------------------------- refuseCompiledSource
	/**
	 * Refuse compiled source :
	 *
	 * - When getSource() will be called, this always be the original file
	 * - Free source if was already loaded with acceptance of compiled source
	 */
	public function refuseCompiledSource()
	{
		if ($this->accept_compiled_source) {
			$this->accept_compiled_source = false;
			$this->free(0);
		}
	}

	//------------------------------------------------------------------------------------ searchFile
	/**
	 * Search the reflection source file into required files
	 *
	 * @param $class_name string   The searched class name
	 * @param $files      string[] The possible files that may contain the class definition
	 * @return boolean true if the file has been found, else false
	 */
	public function searchFile(string $class_name, array $files) : bool
	{
		static $already = [];

		foreach ($files as $key => $file_name) {
			$file_name = Paths::getRelativeFileName($file_name);
			if ($already[$file_name]) {
				unset($files[$key]);
			}
			else {
				$buffer = file_get_contents($file_name);
				if (str_contains($buffer, 'class ' . $class_name)) {
					$this->file_name = $file_name;
					$already = [];
					return true;
				}
				$already[$file_name] = true;
			}
		}

		foreach ($files as $file_name) {
			$source = self::$cache[$file_name]
				?? self::$cache[$class_name]
				?? new Reflection_Source($file_name);
			if ($this->searchFile($class_name, array_keys($source->getRequires()))) {
				$already = [];
				return true;
			}
		}

		$already = [];
		return false;
	}

	//------------------------------------------------------------------------------------- setSource
	/**
	 * Sets the new source code
	 *
	 * Internals : Every properties but the file name are reset to zero by this change.
	 * If you modify the source into a tokens loop, you should set $reset to false to avoid
	 * strange things into your tokens structure.
	 *
	 * @param $source string
	 * @param $reset  boolean
	 * @return Reflection_Source
	 */
	public function setSource(string $source, bool $reset = true) : Reflection_Source
	{
		$this->changed = true;
		if ($reset) {
			$this->free(0);
		}
		$this->source = $source;
		if ($class_name = $this->getFirstClassName()) {
			if (isset(self::$cache[$class_name])) {
				return (self::$cache[$class_name] === $this)
					? $this
					: self::$cache[$class_name]->setSource($source, $reset);
			}
			else {
				self::$cache[$class_name] = $this;
			}
		}
		return $this;
	}

}
