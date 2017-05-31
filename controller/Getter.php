<?php
namespace ITRocks\Framework\Controller;

use ITRocks\Framework\Application;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Tools\Namespaces;

/**
 * Gets the active class for a given base name feature, suffix and file extension
 *
 * Used by Main, View and View\Html\Engine
 */
abstract class Getter
{

	//------------------------------------------------------------------------------------------- get
	/**
	 * @param $base_class   string The base name for the class, ie 'ITRocks\Framework\User'
	 * @param $feature_name string The name of the feature, ie 'dataList'
	 * @param $suffix       string Class suffix, ie 'Controller', 'View'
	 * @param $extension    string File extension, ie 'php', 'html'
	 * @param $class_form   boolean true to use 'Feature_Class' naming instead of 'featureClass'
	 * @return string[] [$class, $method]
	 */
	static public function get($base_class, $feature_name, $suffix, $extension, $class_form = true)
	{
		// $feature_class : 'featureName' transformed into 'Feature_Name'
		// $feature_what : is $feature_class or $feature_name depending on $class_name
		$_suffix             = $suffix ? ('_' . $suffix) : '';
		$application_classes = Application::current()->getClassesTree();
		$class_name          = $base_class;
		$ext                 = DOT . $extension;
		$feature_class       = Names::methodToClass($feature_name);
		$feature_what        = $class_form ? $feature_class : $feature_name;
		$method              = 'run';

		// $classes : the controller class name and its parents and traits
		// ['Vendor\Application\Module\Class_Name' => '\Module\Class_Name']
		$classes = self::getClasses($class_name);

		// Looking for specific controller for each application
		$application_class = reset($application_classes);
		do {
			$namespace = Namespaces::of($application_class);

			// for the controller class and its parents
			foreach ($classes as $short_class_name) {
				$class_name = $namespace . BS . $short_class_name;
				$path       = strtolower(str_replace(BS, SL, $class_name));
if (isset($GLOBALS['D'])) echo '- try A1 ' . $path . SL . $feature_what . $_suffix . $ext . BR;
				if (file_exists($path . SL . $feature_what . $_suffix . $ext)) {
					$class = $class_name . BS . $feature_what . $_suffix;
					break 2;
				}
if (isset($GLOBALS['D'])) echo '- try A2 ' . $path . SL . strtolower($feature_class) . SL . $feature_what . $_suffix . $ext . BR;
				if (file_exists($path . SL . strtolower($feature_class) . SL . $feature_what . $_suffix . $ext)) {
					$class = $class_name . BS . $feature_class . BS . $feature_what . $_suffix;
					break 2;
				}
if (isset($GLOBALS['D']) && $suffix) echo '- try A3 ' . $path . SL . strtolower($feature_class) . SL . $suffix . $ext . BR;
				if ($suffix && file_exists($path . SL . strtolower($feature_class) . SL . $suffix . $ext)) {
					$class = $class_name . BS . $feature_class . BS . $suffix;
					break 2;
				}
if (isset($GLOBALS['D'])) echo '- try A4 ' . Names::classToPath($class_name) . '_' . $feature_what . $_suffix . $ext . BR;
				if (file_exists(
					Names::classToPath($class_name) . '_' . $feature_what . $_suffix . $ext
				)) {
					$class = $class_name . '_' . $feature_what . $_suffix;
					break 2;
				}
if (isset($GLOBALS['D']) && $suffix && $extension != 'html') echo '- try A5 ' . $path . SL . $suffix . $ext . ($extension != 'html' ? ' (::run' . ucfirst($feature_name) . ')' : '') . BR;
				if (
					$suffix
					&& $extension != 'html'
					&& file_exists($path . SL . $suffix . $ext)
					&& method_exists($class_name . BS . $suffix, 'run' . ucfirst($feature_name))
				) {
					$class  = $class_name . BS . $suffix;
					$method = 'run' . ucfirst($feature_name);
					break 2;
				}
if (isset($GLOBALS['D']) && $suffix) echo '- try A6 ' . $path . SL . $suffix . $ext . BR;
				if ($suffix && $ext && file_exists($path . SL . $suffix . $ext)) {
					$class = $class_name . BS . $suffix;
					break 2;
				}
			}

			// next application is the parent one
			$application_class = next($application_classes);
		} while ($application_class);

		// Looking for default controller for each application
		if (empty($class)) {
			reset($application_classes);
			do {
				// looking for default controller
				$path = strtolower(str_replace(BS, SL, $namespace));
if (isset($GLOBALS['D']) && $suffix) echo '- try B1 ' . $path . SL . strtolower($feature_class) . SL . $suffix . $ext . BR;
				if ($suffix && file_exists($path . SL . strtolower($feature_class) . SL . $suffix . $ext)) {
					$class = $namespace . BS . $feature_class . BS . $suffix;
					break;
				}
if (isset($GLOBALS['D'])) echo '- try B2 ' . $path . SL . strtolower($feature_class) . SL . $feature_what . $_suffix . $ext . BR;
				if (file_exists(
					$path . SL . strtolower($feature_class) . SL . $feature_what . $_suffix . $ext
				)) {
					$class = $namespace . BS . $feature_class . BS . $feature_what . $_suffix;
					break;
				}
if (isset($GLOBALS['D']) && $suffix) echo '- try B3 ' . $path . SL . 'widget' . SL . strtolower($feature_class) . SL . $suffix . $ext . BR;
				if ($suffix && file_exists(
					$path . SL . 'widget' . SL . strtolower($feature_class) . SL . $suffix . $ext
				)) {
					$class = $namespace . BS . 'Widget' . BS . $feature_class . BS . $suffix;
					break;
				}
if (isset($GLOBALS['D'])) echo '- try B4 ' . $path . SL . 'widget' . SL . strtolower($feature_class) . SL . $feature_what . $_suffix . $ext . BR;
				if (file_exists(
					$path . SL . 'widget' . SL . strtolower($feature_class) . SL
					. $feature_what . $_suffix . $ext
				)) {
					$class = $namespace . BS . 'Widget' . BS . $feature_class . BS
						. $feature_what . $_suffix;
					break;
				}
if (isset($GLOBALS['D']) && $suffix) echo '- try B5 ' . $path . SL . 'webservice' . SL . strtolower($feature_class) . SL . $suffix . $ext . BR;
				if ($suffix && file_exists(
						$path . SL . 'webservice' . SL . strtolower($feature_class) . SL . $suffix . $ext
					)) {
					$class = $namespace . BS . 'Webservice' . BS . $feature_class . BS . $suffix;
					break;
				}
if (isset($GLOBALS['D'])) echo '- try B6 ' . $path . SL . 'webservice' . SL . strtolower($feature_class) . SL . $feature_what . $_suffix . $ext . BR;
				if (file_exists(
					$path . SL . 'webservice' . SL . strtolower($feature_class) . SL
					. $feature_what . $_suffix . $ext
				)) {
					$class = $namespace . BS . 'Webservice' . BS . $feature_class . BS
					. $feature_what . $_suffix;
					break;
				}

				// next application is the parent one
			} while(next($application_classes));

			// Looking for direct feature call, without using any controller
			static $last_controller_class  = '';
			static $last_controller_method = '';
			if (
				empty($class)
				&& (
					(strpos($suffix, 'View') === false)
					&& ($extension !== 'html')
					&& (
						($last_controller_class  !== $base_class)
						&& ($last_controller_method !== $feature_name)
					)
				)
			) {
				if (strpos($suffix, 'Controller') !== false) {
					$last_controller_class  = $base_class;
					$last_controller_method = $feature_name;
				}
if (isset($GLOBALS['D'])) echo '- try C1 ' . $base_class . '::' . $feature_name . BR;
				if (method_exists($base_class, $feature_name)) {
					$class  = $base_class;
					$method = $feature_name;
				}
			}

			// Looking for default controller for each application
			if (empty($class) && $suffix) {
				reset($application_classes);
				// $suffix == 'Html_View' => $sub = 'View/Html', $suffix = 'View'
				if (strpos($suffix, '_')) {
					$elements = explode('_', $suffix);
					$sub      = join(SL, array_reverse($elements));
					$suffix   = end($elements);
				}
				// $suffix == 'Controller' => $sub = 'Controller', $suffix = 'Controller'
				else {
					$sub = $suffix;
				}
				do {
if (isset($GLOBALS['D'])) echo '- try C2 ' . $path . SL . strtolower($sub) . '/Default_' . $suffix . $ext . BR;
					if (file_exists($path . SL . strtolower($sub) . '/Default_' . $suffix . $ext)) {
						$class = $namespace . BS . str_replace(SL, BS, $sub) . BS . 'Default_' . $suffix;
						break;
					}
				} while (next($application_classes));
			}

		}

		$result = [isset($class) ? $class : null, $method];
if (isset($GLOBALS['D']) && isset($class)) echo '- FOUND ' . ($extension != 'html' ? join('::', $result) : Names::classToPath($class) . $ext). BR;
		return $result;
	}

	//------------------------------------------------------------------------------------ getClasses
	/**
	 * Get classes we can get from, starting from the actual lower descendant
	 *
	 * @param $class_name string
	 * @return string[] key is the full name of each class, value is it without 'Vendor/Project/'
	 */
	static private function getClasses($class_name)
	{
		$classes = [];

		do {
			$classes[$class_name] = self::classNameWithoutVendorProject($class_name);
			if (class_exists($class_name)) {
				$reflection_class = new Reflection_Class(Builder::className($class_name));
				// @extends
				$extends_annotations = $reflection_class->getListAnnotations('extends');
				foreach ($extends_annotations as $extends_annotation) {
					foreach ($extends_annotation->values() as $extends) {
						$classes[$extends] = self::classNameWithoutVendorProject($extends);
					}
				}
				// use (traits)
				$classes = array_merge($classes, self::getTraitsRecursive($reflection_class));
				// parent classes
				$class_name = get_parent_class($class_name);
			}
			else {
				$class_name = null;
			}
		} while ($class_name);

		return $classes;
	}

	//---------------------------------------------------------------------------- getTraitsRecursive
	/**
	 * Get traits we can get from, starting from the actual class / trait
	 *
	 * @param $class Reflection_Class
	 * @return string[] key is the full name of each trait, value is it without 'Vendor/Project/'
	 */
	static private function getTraitsRecursive(Reflection_Class $class)
	{
		$traits = [];
		foreach ($class->getTraits() as $trait) {
			$traits[$trait->name] = self::classNameWithoutVendorProject($trait->name);
			$traits               = array_merge($traits, self::getTraitsRecursive($trait));
		}
		return $traits;
	}

	//----------------------------------------------------------------- classNameWithoutVendorProject
	/**
	 * Returns the name of the class, without the beginning 'Vendor\Project\'
	 *
	 * @param $class_name string 'Vendor\Project\Namespace\Class_Name'
	 * @return string 'Namespace\Class_Name'
	 */
	static private function classNameWithoutVendorProject($class_name)
	{
		return explode(BS, $class_name, 3)[2];
	}

}
