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
	 * @param $class_name string
	 * @param $traits     string[]
	 * @return string the full name of the built class
	 */
	public static function build($class_name, $traits = array())
	{
		$key = implode(".", $traits);
		if (isset(self::$builds[$class_name][$key])) {
			return self::$builds[$class_name][$key];
		}
		else {
			$count = isset(self::$builds[$class_name]) ? count(self::$builds[$class_name]) : null;
			$traits_names = "\\" . implode(", \\", $traits);
			$namespace = Namespaces::of($class_name) . "\\Built$count";
			$short_class = Namespaces::shortClassName($class_name);
			$source = "namespace $namespace {"
			. " final class $short_class"
			. " extends \\$class_name"
			. " { use $traits_names; }"
			. " }";
			eval($source);
			$built_class = $namespace . "\\" . $short_class;
			self::$builds[$class_name][$key] = $built_class;
			return $built_class;
		}
	}

}
