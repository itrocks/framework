<?php
namespace ITRocks\Framework\Builder;

use ITRocks\Framework\Application;
use ITRocks\Framework\Dao;
use ITRocks\Framework\PHP\Dependency;
use ITRocks\Framework\PHP\Reflection_Class;
use ITRocks\Framework\Tools\Namespaces;

/**
 * The class builder builds dynamically a virtual class composed of an existing class and additional traits
 *
 * @todo remove dependencies
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
			$interfaces = [];
			$traits = [];
			foreach ($interfaces_traits as $interface_trait) {
				$class = Reflection_Class::of($interface_trait);
				if ($class->isInterface()) {
					$interfaces[$interface_trait] = $interface_trait;
				}
				elseif ($class->isTrait()) {
					foreach ($class->getListAnnotation('implements')->values() as $implements) {
						$interfaces[$implements] = $implements;
					}
					$level = 0;
					$extends_annotations = $class->getListAnnotations('extends');
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
				}
				else {
					trigger_error(
						'Unknown interface/trait ' . DQ . $interface_trait . DQ
						. ' while building ' . $class_name,
						E_USER_ERROR
					);
				}
			}
			$built_class = self::buildClass($class_name, $interfaces, $traits, $get_source);
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
	 * @param $get_source  boolean if true, get built [$name, $source] instead of $name
	 * @return string|string[] generated class name
	 */
	private static function buildClass($class_name, array $interfaces, array $traits, $get_source)
	{
		if (!$traits) $traits = [0 => []];
		end($traits);
		$end_level = key($traits);
		$short_class = Namespaces::shortClassName($class_name);
		$namespace_prefix = Namespaces::of(self::builtClassName($class_name));
		$namespace = $built_class = null;
		foreach ($traits as $level => $class_traits) {
			// must be set before $namespace (extends last class)
			$extends = BS . (isset($namespace) ? ($namespace . BS . $short_class) : $class_name);
			$end = ($level == $end_level);
			$count = isset(self::$builds[$class_name]) ? count(self::$builds[$class_name]) : '';
			$sub_count = $end ? '' : (BS . 'Sub' . ($end - $level));
			$interfaces_names = ($end && $interfaces) ? (BS . join(', ' . BS, $interfaces)) : '';
			$traits_names = $class_traits ? join(';' . LF . TAB . 'use ' . BS, $class_traits) : '';
			$namespace = $namespace_prefix . $count . $sub_count;
			$built_class = $namespace . BS . $short_class;
			$source = 'namespace ' . $namespace . ($get_source ? ';' : ' {') . LF . LF
				. '/** Built ' . $short_class . ' class */' . LF
				. 'class ' . $short_class . ' extends ' . $extends
				. ($interfaces_names ? (LF . TAB . 'implements ' . $interfaces_names) : '')
				. LF . '{' . LF
				. ($traits_names ? (TAB . 'use ' . BS . $traits_names . ';' . LF) : '')
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
			return $namespace . $class_name;
		}
		return false;
	}

	//----------------------------------------------------------------------------- getBuiltNameSpace
	/**
	 * Returns the prefix namespace for built classes
	 *
	 * @return null|string
	 */
	public static function getBuiltNameSpace()
	{
		static $namespace;
		if (!isset($namespace) && $application = Application::current()) {
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
		if ($namespace = self::getBuiltNameSpace()) {
			return substr($class_name, 0, strlen($namespace)) == $namespace;
		}
		return false;
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
			return str_replace($namespace, '', $class_name);
		}
		return false;
	}

}
