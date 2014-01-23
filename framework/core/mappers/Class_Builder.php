<?php
namespace SAF\Framework;

/**
 * The class builder builds dynamically a virtual class composed of an existing class and additional traits
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
	private static $builds = array();

	//----------------------------------------------------------------------------------------- build
	/**
	 * @param $class_name        string The base class name
	 * @param $interfaces_traits string[] The interfaces and traits names list
	 * @return string the full name of the built class
	 */
	public static function build($class_name, $interfaces_traits = array())
	{
		$key = implode(".", $interfaces_traits);
		if (isset(self::$builds[$class_name][$key])) {
			return self::$builds[$class_name][$key];
		}
		else {
			$interfaces = array();
			$traits = array();
			foreach ($interfaces_traits as $interface_trait) {
				$interface_trait = Namespaces::defaultFullClassName($interface_trait, $class_name);
				if ($interface_trait[0] != "\\") {
					$interface_trait = "\\" . $interface_trait;
				}
				if (interface_exists($interface_trait)) {
					$interfaces[$interface_trait] = $interface_trait;
				}
				elseif (trait_exists($interface_trait)) {
					$reflection = new Reflection_Class($interface_trait);
					foreach ($reflection->getListAnnotation("implements")->values() as $implements) {
						$implements = Namespaces::defaultFullClassName($implements, $interface_trait);
						$interfaces[$implements] = $implements;
					}
					$level = 0;
					foreach ($reflection->getListAnnotation("extends")->values() as $extends) {
						$extends = Namespaces::defaultFullClassName($extends, $interface_trait);
						if (trait_exists($extends)) {
							foreach ($traits as $trait_level => $trait_names) {
								if ($trait_names[$extends]) {
									$level = max($level, $trait_level + 1);
								}
							}
						}
					}
					$traits[$level][$interface_trait] = $interface_trait;
				}
				else {
					trigger_error(
						"Unknown interface/trait \"$interface_trait\" while building $class_name",
						E_USER_ERROR
					);
				}
			}
			$built_class = self::buildClass($class_name, $interfaces, $traits);
			self::$builds[$class_name][$key] = $built_class;
			return $built_class;
		}
	}

	//------------------------------------------------------------------------------------ buildClass
	/**
	 * @param $class_name  string
	 * @param $interfaces  string[]
	 * @param $traits      string[]
	 * @return string generated class name
	 */
	private static function buildClass($class_name, $interfaces, $traits)
	{
		if (!$traits) $traits = array(0 => array());
		end($traits);
		$end_level = key($traits);
		$namespace = $short_class = null;
		foreach ($traits as $level => $class_traits) {
			// must be set before $shot_class and $namespace (extends last class)
			$extends = "\\" . (isset($short_class) ? ($namespace . "\\" . $short_class) : $class_name);
			$end = ($level == $end_level);
			$final = $end ? "final " : "";
			$count = isset(self::$builds[$class_name]) ? count(self::$builds[$class_name]) : "";
			$sub_count = $end ? "" : ("_" . ($end - $level));
			$namespace = Namespaces::of($class_name) . "\\Built" . $count . $sub_count;
			$interfaces_names = ($end && $interfaces) ? implode(", ", $interfaces) : "";
			$traits_names = $class_traits ? implode(", ", $class_traits ) : "";
			$short_class = Namespaces::shortClassName($class_name);
			$source = "namespace $namespace {\n"
				. $final . "class $short_class"
				. "\nextends " . $extends
				. ($interfaces_names ? "\nimplements $interfaces_names" : "")
				. "\n{\n"
				. ($traits_names ? " use $traits_names;" : "")
				. "\n}\n"
				. "}";
			self::buildClassSource($namespace . "\\" . $short_class, $source);
		}
		return $namespace . "\\" . $short_class;
	}

	//------------------------------------------------------------------------------ buildClassSource
	/**
	 * @param $class_name string
	 * @param $source     string
	 */
	private static function buildClassSource(
		/** @noinspection PhpUnusedParameterInspection */ $class_name, $source
	) {
		echo "build class $class_name<br>";
		eval($source);
	}

}
