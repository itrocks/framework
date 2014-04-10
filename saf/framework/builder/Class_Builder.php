<?php
namespace SAF\Framework\Builder;

use SAF\Framework\Application;
use SAF\Framework\Reflection\Reflection_Class;
use SAF\Framework\Tools\Namespaces;

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
	 * Keys are the name of the class and the
	 *
	 * @var string[]
	 */
	private static $builds = [];

	//----------------------------------------------------------------------------------------- build
	/**
	 * @param $class_name        string The base class name
	 * @param $interfaces_traits string[] The interfaces and traits names list
	 * @param $get_source        boolean if true, get built [$name, $source] instead of $name
	 * @return string|string[] the full name of the built class
	 */
	public static function build($class_name, $interfaces_traits = [], $get_source = false)
	{
		$key = join(DOT, $interfaces_traits);
		if (isset(self::$builds[$class_name][$key])) {
			return self::$builds[$class_name][$key];
		}
		else {
			$interfaces = [];
			$traits = [];
			foreach ($interfaces_traits as $interface_trait) {
				$interface_trait = Namespaces::defaultFullClassName($interface_trait, $class_name);
				if (interface_exists($interface_trait)) {
					$interfaces[$interface_trait] = $interface_trait;
				}
				elseif (trait_exists($interface_trait)) {
					$reflection = new Reflection_Class($interface_trait);
					foreach ($reflection->getListAnnotation('implements')->values() as $implements) {
						$implements = Namespaces::defaultFullClassName($implements, $interface_trait);
						$interfaces[$implements] = $implements;
					}
					$level = 0;
					foreach ($reflection->getListAnnotation('extends')->values() as $extends) {
						$extends = Namespaces::defaultFullClassName($extends, $interface_trait);
						if (trait_exists($extends)) {
							foreach ($traits as $trait_level => $trait_names) {
								if (isset($trait_names[$extends])) {
									$level = max($level, $trait_level + 1);
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
	 * @param $interfaces  string[]
	 * @param $traits      string[]
	 * @param $get_source        boolean if true, get built [$name, $source) instead of $name
	 * @return string|string[] generated class name
	 */
	private static function buildClass($class_name, $interfaces, $traits, $get_source)
	{
		if (!$traits) $traits = [0 => []];
		end($traits);
		$end_level = key($traits);
		$namespace = $short_class = $built_class = null;
		foreach ($traits as $level => $class_traits) {
			// must be set before $shot_class and $namespace (extends last class)
			$extends = BS . (isset($short_class) ? ($namespace . BS . $short_class) : $class_name);
			$end = ($level == $end_level);
			$final = $end ? 'final ' : '';
			$count = isset(self::$builds[$class_name]) ? count(self::$builds[$class_name]) : '';
			$sub_count = $end ? '' : (BS . 'Sub' . ($end - $level));
			$namespace = array_slice(explode(BS, Namespaces::of($class_name)), 1);
			$left = Namespaces::of(Application::current());
			$namespace = $left . BS . 'Built' . BS . join(BS, $namespace) . $count . $sub_count;
			$interfaces_names = ($end && $interfaces) ? (BS . join(', ' . BS, $interfaces)) : '';
			$traits_names = $class_traits ? join(';' . LF . TAB . 'use ' . BS, $class_traits) : '';
			$short_class = Namespaces::shortClassName($class_name);
			$built_class = $namespace . BS . $short_class;
			$source = 'namespace ' . $namespace . ($get_source ? ';' : ' {') . LF . LF
				. '/** Built ' . $short_class . ' class */' . LF
				. $final . 'class ' . $short_class . ' extends ' . $extends
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

}
