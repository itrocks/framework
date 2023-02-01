<?php
namespace ITRocks\Framework\Builder;

use ITRocks\Framework\Application;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\PHP\Dependency;
use ITRocks\Framework\PHP\Reflection_Class;
use ITRocks\Framework\PHP\Reflection_Source;
use ITRocks\Framework\Reflection\Attribute\Class_\Extends_;
use ITRocks\Framework\Session;
use ITRocks\Framework\Tools\Namespaces;

/**
 * The class builder builds dynamically a virtual class composed of an existing class and additional
 * traits
 *
 * TODO remove dependencies
 */
class Class_Builder
{

	//--------------------------------------------------------------------------------------- $builds
	/**
	 * $builds stores already built classes
	 *
	 * Keys are the name of the class and most of the interfaces / traits names separated by dots
	 *
	 * @var array string[][]
	 */
	private static array $builds = [];

	//-------------------------------------------------------------------------------------- $sources
	/**
	 * Keep built sources, as it may be asked twice for reflection / annotations read during compile
	 *
	 * @var array
	 */
	private static array $sources = [];

	//----------------------------------------------------------------------------------------- build
	/**
	 * @param $class_name        string The base class name
	 * @param $interfaces_traits string[] The interfaces and traits names list
	 * @param $get_source        boolean if true, get built [$name, $source] instead of $name
	 * @return string|string[] the full name of the built class
	 */
	public function build(string $class_name, array $interfaces_traits = [], bool $get_source = false)
		: array|string
	{
		$key = join(DOT, $interfaces_traits);
		if (isset(static::$builds[$class_name][$key])) {
			return $get_source
				? [static::$sources[$class_name][$key]]
				: static::$builds[$class_name][$key];
		}
		$classes        = [];
		$traits_before  = [];
		$traits_extends = [];
		foreach ($interfaces_traits as $position => $interface_trait) {
			if (substr($interface_trait, 0, 1) === AT) {
				continue;
			}
			$class                     = Reflection_Class::of($interface_trait);
			$classes[$interface_trait] = $class;
			if ($class->isTrait()) {
				$extends_attributes = Extends_::of($class);
				foreach ($extends_attributes as $extends_attribute) {
					foreach ($extends_attribute->extends as $extends) {
						if (Dao::search(
							['class_name' => $extends, 'declaration' => Dependency::T_TRAIT_DECLARATION],
							Dependency::class
						)) {
							$traits_extends[$interface_trait][$extends] = $extends;
						}
					}
				}
				if (isset($traits_extends[$interface_trait])) {
					foreach ($traits_extends[$interface_trait] as $extends) {
						if (in_array($extends, $interfaces_traits) && !isset($traits_before[$extends])) {
							unset($interfaces_traits[$position]);
							$interfaces_traits[] = $interface_trait;
							continue 2;
						}
					}
				}
				$traits_before[$interface_trait] = true;
			}
		}
		$annotations = [];
		$interfaces  = [];
		$traits      = [];
		foreach ($interfaces_traits as $interface_trait) {
			// @annotation
			if (substr($interface_trait, 0, 1) === AT) {
				$annotations[] = $interface_trait;
				continue;
			}
			// Interface\Name::class
			$class = $classes[$interface_trait];
			if ($class->isInterface()) {
				$interfaces[$interface_trait] = $interface_trait;
				continue;
			}
			// Trait\Name::class
			if ($class->isTrait()) {
				foreach ($class->getListAnnotation('implements')->values() as $implements) {
					$interfaces[$implements] = $implements;
				}
				$level = 0;
				if (isset($traits_extends[$interface_trait])) {
					foreach ($traits_extends[$interface_trait] as $extends) {
						foreach ($traits as $trait_level => $trait_names) {
							if (isset($trait_names[$extends])) {
								$level = max($level, $trait_level + 1);
							}
						}
					}
				}
				$traits[$level][$interface_trait] = $interface_trait;
				continue;
			}
			// anything else
			trigger_error(
				'Unknown interface/trait ' . DQ . $interface_trait . DQ
				. ' while building ' . $class_name,
				E_USER_ERROR
			);
		}
		$built_class = $this->buildClass(
			$class_name, $interfaces, $traits, $annotations, $get_source, $key
		);
		if (!$get_source) {
			static::$builds[$class_name][$key] = $built_class;
		}
		return $built_class;
	}

	//------------------------------------------------------------------------------------ buildClass
	/**
	 * @param $class_name  string
	 * @param $interfaces  array string[][]
	 * @param $traits      array string[][]
	 * @param $annotations array string[]
	 * @param $get_source  boolean if true, get built [$name, $source] instead of $name
	 * @param $key         string
	 * @return string|string[] generated class name
	 */
	protected function buildClass(
		string $class_name, array $interfaces, array $traits, array $annotations, bool $get_source,
		string $key
	) : array|string
	{
		if (!$traits) {
			$traits = [0 => []];
		}
		end($traits);
		$end_level        = key($traits);
		$short_class      = Namespaces::shortClassName($class_name);
		$namespace_prefix = Namespaces::of(static::builtClassName($class_name));
		$namespace        = $built_class = null;
		$source           = null;
		$class            = Reflection_Source::ofClass($class_name)->getFirstClass();
		foreach ($traits as $level => $class_traits) {
			// must be set before $namespace (extends last class)
			$extends   = (isset($namespace) ? ($namespace . BS . $short_class) : $class_name);

			$end       = ($level === $end_level);
			$count     = isset(static::$builds[$class_name]) ? count(static::$builds[$class_name]) : '';
			$sub_count = $end ? '' : (BS . 'Sub' . ($end - $level));

			if ($class->getType() === T_TRAIT) {
				array_unshift($class_traits, $extends);
				$abstract = '';
				$extends  = '';
				$type     = 'trait';
			}
			else {
				$abstract = ($class->isAbstract() || !$end) ? 'abstract ' : '';
				$extends  = ' extends ' . BS . $extends;
				$type     = 'class';
			}

			$namespace   = $namespace_prefix . $count . $sub_count;
			$built_class = $this->buildClassName($namespace, $short_class);

			$annotations_code = ($end && $annotations)
				? (' *' . LF . ' * ' . join(LF . ' * ', $annotations) . LF)
				: '';
			$interfaces_names = ($end && $interfaces)
				? (LF . TAB . 'implements ' . BS . join(', ' . BS, $interfaces))
				: '';
			$traits_names = $class_traits
				? (TAB . 'use ' . BS . join(';' . LF . TAB . 'use ' . BS, $class_traits) . ';' . LF)
				: '';

			$source = 'namespace ' . $namespace . ($get_source ? ';' : ' /*BUILT*/{') . LF . LF
				. '/**' . LF
				. ' * Built ' . $short_class . ' ' . $type . LF
				. $annotations_code
				. ' */' . LF
				. $abstract . $type . ' ' . $short_class . $extends
				. $interfaces_names
				. LF . '{' . LF
				. $traits_names
				. LF . '}' . LF
				. ($get_source ? '' : (LF . '}/*BUILT*/' . LF));

			if ($get_source === true) {
				$get_source = [$built_class => $source];
			}
			elseif ($get_source) {
				$get_source[$built_class] = $source;
			}
			else {
				$this->buildClassSource($built_class, $source);
			}
		}
		if (!$get_source) {
			$source = str_replace([' /*BUILT*/{', LF . '}/*BUILT*/' . LF], [';', ''], $source);
		}
		static::$sources[$class_name][$key] = $source;
		return $get_source ?: $built_class;
	}

	//-------------------------------------------------------------------------------- buildClassName
	/**
	 * Final part of the building of the name of the class
	 *
	 * Default behaviour is to concatenate namespace and class name.
	 * More can be done by child classes.
	 *
	 * @param $namespace        string
	 * @param $short_class_name string
	 * @return string
	 */
	protected function buildClassName(string $namespace, string $short_class_name) : string
	{
		return $namespace . BS . $short_class_name;
	}

	//------------------------------------------------------------------------------ buildClassSource
	/**
	 * @param $class_name string
	 * @param $source     string
	 */
	protected function buildClassSource(
		/* @noinspection PhpUnusedParameterInspection */ string $class_name, string $source
	) : void
	{
		eval($source);
	}

	//-------------------------------------------------------------------------------- builtClassName
	/**
	 * Gets built class name for a source class name
	 *
	 * @param $class_name string ie 'ITRocks\Framework\Module\Class_Name'
	 * @return ?string ie 'Vendor\Application\Built\ITRocks\Framework\Module\Class_Name'
	 * @see Class_Builder::sourceClassName()
	 */
	public static function builtClassName(string $class_name) : ?string
	{
		if (static::isBuilt($class_name)) {
			return $class_name;
		}
		if ($namespace = static::getBuiltNameSpace()) {
			// TODO wait for process of database migration to rollback above. see #88021
			// SM: temporarily disable patch #88021 (vendor is part of built class_name)
			//return $namespace . $class_name;
			return $namespace . rParse($class_name, BS, 1, true);
		}
		return null;
	}

	//----------------------------------------------------------------------------- getBuiltNameSpace
	/**
	 * Returns the prefix namespace for built classes
	 *
	 * @return ?string
	 */
	public static function getBuiltNameSpace() : ?string
	{
		static $namespace = null;
		if (!isset($namespace) && ($application = Application::current())) {
			$namespace = $application->getNamespace() . BS . 'Built' . BS;
		}
		return $namespace;
	}

	//--------------------------------------------------------------------------------------- isBuilt
	/**
	 * Returns true if class name is a built class name
	 *
	 * A built class has a namespace beginning with 'Vendor\Application\Built\'
	 *
	 * @param $class_name string
	 * @return boolean
	 */
	public static function isBuilt(string $class_name) : bool
	{
		return (($namespace = static::getBuiltNameSpace()))
			&& str_starts_with($class_name, $namespace);
	}

	//------------------------------------------------------------------------------- sourceClassName
	/**
	 * Gets source class name for a built class name
	 *
	 * @param $class_name string ie 'Vendor\Application\Built\ITRocks\Framework\Module\Class_Name'
	 * @return string ie 'ITRocks\Framework\Module\Class_Name'
	 * @see Class_Builder::builtClassName()
	 */
	public static function sourceClassName(string $class_name) : string
	{
		if (!static::isBuilt($class_name)) {
			return $class_name;
		}
		if (static::getBuiltNameSpace()) {
			// SM: temporarily disable patch #88021 (vendor is part of built class_name)
			//return str_replace($namespace, '', $class_name);
			// TODO wait for process of database migration to rollback above. see #88021
			/** @var $builder Builder */
			$builder = Session::current()->plugins->get(Builder::class);
			// SM: Note this is buggy during Application update()
			return $builder->sourceClassName($class_name);
		}
		return false;
	}

}
