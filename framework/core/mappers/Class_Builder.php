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
			$count = isset(self::$builds[$class_name]) ? count(self::$builds[$class_name]) : null;
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
					$traits[$interface_trait] = $interface_trait;
					foreach (
						(new Reflection_Class($interface_trait))->getListAnnotation("implements")->values()
						as $implements
					) {
						$implements = Namespaces::defaultFullClassName($implements, $interface_trait);
						$interfaces[$implements] = $implements;
					}
				}
				else {
					trigger_error(
						"Unknown interface/trait \"$interface_trait\" while building $class_name",
						E_USER_ERROR
					);
				}
			}
			$interfaces_names = $interfaces ? implode(", ", $interfaces) : "";
			$traits_names = $traits ? implode(", ", $traits) : "";
			$namespace = Namespaces::of($class_name) . "\\Built$count";
			$short_class = Namespaces::shortClassName($class_name);
			$source = "namespace $namespace {"
			. " final class $short_class"
			. " extends \\$class_name"
			. ($interfaces_names ? " implements $interfaces_names" : "")
			. " {" . ($traits_names ? " use $traits_names;" : "") . " }"
			. " }";
			eval($source);
			$built_class = $namespace . "\\" . $short_class;
			self::$builds[$class_name][$key] = $built_class;
			return $built_class;
		}
	}

}
