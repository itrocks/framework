<?php
namespace SAF\Framework\Controller;

use SAF\Framework\Application;
use SAF\Framework\Tools\Names;
use SAF\Framework\Tools\Namespaces;

/**
 * Gets the active class for a given base name feature, suffix and file extension
 *
 * Used by Main, View and View\Html\Engine
 */
abstract class Getter
{

	//------------------------------------------------------------------------------------------- get
	/**
	 * @param $base_class   string The base name for the class, ie 'SAF\Framework\User'
	 * @param $feature_name string The name of the feature, ie 'dataList'
	 * @param $suffix       string Class suffix, ie 'Controller', 'View'
	 * @param $extension    string File extension, ie 'php', 'html'
	 * @param $class_form   boolean true to use 'Feature_Class' naming instead of 'featureClass'
	 * @return string[] [$class, $method]
	 */
	static public function get($base_class, $feature_name, $suffix, $extension, $class_form = true)
	{
		// ie : $feature_class = 'featureName' transformed into 'Feature_Name'
		$feature_class = Names::methodToClass($feature_name);
		// $feature_what is $feature_class or $feature_name depending on $class_name
		$feature_what = $class_form ? $feature_class : $feature_name;
		$_suffix = $suffix ? ('_' . $suffix) : '';
		$class_name = $base_class;
		$ext = '.' . $extension;
		$method = 'run';

		// $classes : the controller class name and its parents
		// ['Vendor\Application\Module\Class_Name' => '\Module\Class_Name']
		$classes = [];
		do {
			$classes[$class_name] = substr(
				$class_name, strpos($class_name, BS, strpos($class_name, BS) + 1)
			);
			$class_name = @get_parent_class($class_name);
		} while ($class_name);

		// Looking for specific controller for each application
		$application_class = get_class(Application::current());
		do {
			$namespace = Namespaces::of($application_class);

			// for the controller class and its parents
			foreach ($classes as $short_class_name) {
				$class_name = $namespace . $short_class_name;
				$path = strtolower(str_replace(BS, SL, $class_name));
if (isset($GLOBALS['D'])) echo '- try 1 ' . $path . SL . $feature_what . $_suffix . $ext . '<br>';
				if (file_exists($path . SL . $feature_what . $_suffix . $ext)) {
					$class = $class_name . BS . $feature_what . $_suffix;
					break 2;
				}
if (isset($GLOBALS['D']) && $suffix) echo '- try 2 ' . $path . SL . strtolower($feature_class) . SL . $suffix . $ext . '<br>';
				if ($suffix && file_exists($path . SL . strtolower($feature_class) . SL . $suffix . $ext)) {
					$class = $class_name . BS . $feature_class . SL . $suffix;
					break 2;
				}
if (isset($GLOBALS['D'])) echo '- try 3 ' . Names::classToPath($class_name) . '_' . $feature_what . $_suffix . $ext . '<br>';
				if (file_exists(
					Names::classToPath($class_name) . '_' . $feature_what . $_suffix . $ext
				)) {
					$class = $class_name . '_' . $feature_what . $_suffix;
					break 2;
				}
if (isset($GLOBALS['D']) && $suffix) echo '- try 4 ' . $path . SL . $suffix . $ext . '<br>';
				if (
					$suffix
					&& file_exists($path . SL . $suffix . $ext)
					&& method_exists($class_name . BS . $suffix, 'run' . ucfirst($feature_name))
				) {
					$class = $class_name . BS . $suffix;
					$method = 'run' . ucfirst($feature_name);
					break 2;
				}
			}

			// next application is the parent one
			if (substr($base_class, 0, strlen($namespace)) == $namespace) break;
			$application_class = get_parent_class($application_class);
		} while ($application_class);

		// Looking for default controller for each application
		if (empty($class)) {
			$application_class = get_class(Application::current());
			do {
				// looking for default controller
				$path = strtolower(str_replace(BS, SL, $namespace));
if (isset($GLOBALS['D']) && $suffix) echo '- try 5 ' . $path . SL . strtolower($feature_class) . SL . $suffix . $ext . '<br>';
				if ($suffix && file_exists($path . SL . strtolower($feature_class) . SL . $suffix . $ext)) {
					$class = $namespace . BS . $feature_class . BS . $suffix;
					break;
				}
if (isset($GLOBALS['D']) && $suffix) echo '- try 6 ' . $path . SL . strtolower($feature_class) . SL . $feature_what . $_suffix . $ext . '<br>';
				if ($suffix && file_exists(
					$path . SL . strtolower($feature_class) . SL . $feature_what . $_suffix . $ext
				)) {
					$class = $namespace . BS . $feature_class . BS . $feature_what . $_suffix;
					break;
				}
if (isset($GLOBALS['D']) && $suffix) echo '- try 7 ' . $path . SL . 'widget' . SL . strtolower($feature_class) . SL . $suffix . $ext . '<br>';
				if ($suffix && file_exists(
					$path . SL . 'widget' . SL . strtolower($feature_class) . SL . $suffix . $ext
				)) {
					$class = $namespace . BS . 'Widget' . BS . $feature_class . BS . $suffix;
					break;
				}
if (isset($GLOBALS['D'])) echo '- try 8 ' . $path . SL . 'widget' . SL . strtolower($feature_class) . SL . $feature_what . $_suffix . $ext . '<br>';
				if (file_exists(
					$path . SL . 'widget' . SL . strtolower($feature_class) . SL
					. $feature_what . $_suffix . $ext
				)) {
					$class = $namespace . BS . 'Widget' . BS . $feature_class . BS
						. $feature_what . $_suffix;
					break;
				}

				// next application is the parent one
				$application_class = get_parent_class($application_class);
			} while($application_class);

			// Looking for default controller for each application
			if (empty($class) && $suffix) {
				$application_class = get_class(Application::current());
				// $suffix == 'Html_View' => $sub = 'View/Html', $suffix = 'View'
				if (strpos($suffix, '_')) {
					$elements = explode('_', $suffix);
					$sub = join(SL, array_reverse($elements));
					$suffix = end($elements);
				}
				// $suffix == 'Controller' => $sub = 'Controller', $suffix = 'Controller'
				else {
					$sub = $suffix;
				}
				do {
if (isset($GLOBALS['D'])) echo '- try 9 ' . $path . SL . strtolower($sub) . '/Default_' . $suffix . $ext . '<br>';
					if (file_exists($path . SL . strtolower($sub) . '/Default_' . $suffix . $ext)) {
						$class = $namespace . BS . str_replace(SL, BS, $sub) . BS . 'Default_' . $suffix;
						break;
					}
					$application_class = get_parent_class($application_class);
				} while ($application_class);
			}

		}

		return [isset($class) ? $class : null, $method];
	}

}
