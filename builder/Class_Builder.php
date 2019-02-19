<?php
namespace ITRocks\Framework\Builder;

use ITRocks\Framework\Application;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\PHP\Dependency;
use ITRocks\Framework\PHP\Reflection_Class;
use ITRocks\Framework\PHP\Reflection_Source;
use ITRocks\Framework\Reflection\Annotation\Class_\Extends_Annotation;
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
	 * Keys are the name of the class and the most of interfaces / traits names separated by dots
	 *
	 * @var array string[][]
	 */
	private static $builds = [];

	//----------------------------------------------------------------------------------------- build
	/**
	 * @param $class_name        string The base class name
	 * @param $interfaces_traits string[] The interfaces and traits names list
	 * @param $get_source        boolean if true, get built [$name, $source] instead of $name
	 * @return string|string[] the full name of the built class
	 */
	public static function build($class_name, array $interfaces_traits = [], $get_source = false)
	{
		$key = join(DOT, $interfaces_traits);
		if (isset(self::$builds[$class_name][$key])) {
			return self::$builds[$class_name][$key];
		}
		else {
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
				$class = Reflection_Class::of($interface_trait);
				if ($class->isInterface()) {
					$interfaces[$interface_trait] = $interface_trait;
					continue;
				}
				// Trait\Name::class
				if ($class->isTrait()) {
					foreach ($class->getListAnnotation('implements')->values() as $implements) {
						$interfaces[$implements] = $implements;
					}
					$extends_annotations = Extends_Annotation::allOf($class);
					$level               = 0;
					foreach ($extends_annotations as $extends_annotation) {
						foreach ($extends_annotation->values() as $extends) {
							if (Dao::search(
								['class_name' => $extends, 'declaration' => Dependency::T_TRAIT_DECLARATION],
								Dependency::class
							)) {
								foreach ($traits as $trait_level => $trait_names) {
									if (isset($trait_names[$extends])) {
										$level = max($level, $trait_level + 1);
									}
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
			$built_class = self::buildClass($class_name, $interfaces, $traits, $annotations, $get_source);
			if (!$get_source) {
				self::$builds[$class_name][$key] = $built_class;
			}
			return $built_class;
		}
	}

	//------------------------------------------------------------------------------------ buildClass
	/**
	 * @param $class_name  string
	 * @param $interfaces  array string[][]
	 * @param $traits      array string[][]
	 * @param $annotations array string[]
	 * @param $get_source  boolean if true, get built [$name, $source] instead of $name
	 * @return string|string[] generated class name
	 */
	private static function buildClass(
		$class_name, array $interfaces, array $traits, array $annotations, $get_source
	) {
		if (!$traits) {
			$traits = [0 => []];
		}
		end($traits);
		$end_level        = key($traits);
		$short_class      = Namespaces::shortClassName($class_name);
		$namespace_prefix = Namespaces::of(self::builtClassName($class_name));
		$namespace        = $built_class = null;
		foreach ($traits as $level => $class_traits) {
			// must be set before $namespace (extends last class)
			$extends   = (isset($namespace) ? ($namespace . BS . $short_class) : $class_name);

			$end       = ($level === $end_level);
			$count     = isset(self::$builds[$class_name]) ? count(self::$builds[$class_name]) : '';
			$sub_count = $end ? '' : (BS . 'Sub' . ($end - $level));

			$class = Reflection_Source::ofClass($extends)->getFirstClass();
			if ($class->getType() === T_TRAIT) {
				array_unshift($class_traits, $extends);
				$abstract = '';
				$extends  = '';
				$type     = 'trait';
			}
			else {
				$abstract = $class->isAbstract() ? 'abstract ' : '';
				$extends  = ' extends ' . BS . $extends;
				$type     = 'class';
			}

			$namespace   = $namespace_prefix . $count . $sub_count;
			$built_class = $namespace . BS . $short_class;

			$annotations_code = ($end && $annotations)
				? (' *' . LF . ' * ' . join(LF . ' * ', $annotations) . LF)
				: '';
			$interfaces_names = ($end && $interfaces)
				? (LF . TAB . 'implements ' . BS . join(', ' . BS, $interfaces))
				: '';
			$traits_names = $class_traits
				? (TAB . 'use ' . BS . join(';' . LF . TAB . 'use ' . BS, $class_traits) . ';' . LF)
				: '';

			$source = 'namespace ' . $namespace . ($get_source ? ';' : ' {') . LF . LF
				. '/**' . LF
				. ' * Built ' . $short_class . ' ' . $type . LF
				. $annotations_code
				. ' */' . LF
				. $abstract . $type . ' ' . $short_class . $extends
				. $interfaces_names
				. LF . '{' . LF
				. $traits_names
				. LF . '}' . LF
				. ($get_source ? '' : (LF . '}' . LF));

			if ($get_source === true) {
				$get_source = [$built_class => $source];
			}
			elseif ($get_source) {
				$get_source[$built_class] = $source;
			}
			else {
				self::buildClassSource($built_class, $source);
			}
		}
		return $get_source ?: $built_class;
	}

	//------------------------------------------------------------------------------ buildClassSource
	/**
	 * @param $class_name string
	 * @param $source     string
	 */
	private static function buildClassSource(
		/** @noinspection PhpUnusedParameterInspection */ $class_name, $source
	) {
		eval($source);
	}

	//-------------------------------------------------------------------------------- builtClassName
	/**
	 * Gets built class name for a source class name
	 *
	 * @param $class_name string ie 'ITRocks\Framework\Module\Class_Name'
	 * @return string ie 'Vendor\Application\Built\ITRocks\Framework\Module\Class_Name'
	 * @see Class_Builder::sourceClassName()
	 */
	public static function builtClassName($class_name)
	{
		if (self::isBuilt($class_name)) {
			return $class_name;
		}
		if ($namespace = self::getBuiltNameSpace()) {
			// TODO wait for process of database migration to rollback above. see #88021
			// SM: temporarily disable patch #88021 (vendor is part of built class_name)
			//return $namespace . $class_name;
			return $namespace . rParse($class_name, BS, 1, true);
		}
		return false;
	}

	//----------------------------------------------------------------------------- getBuiltNameSpace
	/**
	 * Returns the prefix namespace for built classes
	 *
	 * @return string|null
	 */
	public static function getBuiltNameSpace()
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
	public static function isBuilt($class_name)
	{
		return ($namespace = self::getBuiltNameSpace())
			? (substr($class_name, 0, strlen($namespace)) === $namespace)
			: false;
	}

	//------------------------------------------------------------------------------- sourceClassName
	/**
	 * Gets source class name for a built class name
	 *
	 * @param $class_name string ie 'Vendor\Application\Built\ITRocks\Framework\Module\Class_Name'
	 * @return string ie 'ITRocks\Framework\Module\Class_Name'
	 * @see Class_Builder::builtClassName()
	 */
	public static function sourceClassName($class_name)
	{
		if (!self::isBuilt($class_name)) {
			return $class_name;
		}
		if ($namespace = self::getBuiltNameSpace()) {
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
