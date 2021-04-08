<?php
namespace ITRocks\Framework\Controller;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Reflection\Annotation\Class_\Extends_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Method;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Tools\Namespaces;

/**
 * Gets the active class for a given base name feature, suffix and file extension
 *
 * Used by Main, View and View\Html\Engine
 */
abstract class Getter
{

	//----------------------------------------------------------------- classNameWithoutVendorProject
	/**
	 * Returns the name of the class, without the beginning 'Vendor\Project\'
	 *
	 * Applies with :
	 * - Vendor\Project\ itrocks sub projects
	 * - Vendor\ itrocks core projects
	 *
	 * @example
	 * When the Vendor\Project\Application exists :
	 * 'Vendor\Project\Namespace\Class_Name' => 'Namespace\Class_Name'
	 * @example
	 * When no Vendor\Project\Application class is found :
	 * 'Vendor\Namespace\Class_Name' => 'Namespace\Class_Name'
	 * @example
	 * When the name of the class is at project-level :
	 * 'Vendor\Class_Name' => 'Class_Name'
	 * @param $class_name string
	 * @return string
	 */
	static public function classNameWithoutVendorProject(string $class_name) : string
	{
		if (!substr_count($class_name, BS)) {
			return $class_name;
		}
		if (substr_count($class_name, BS) == 1) {
			$without = substr($class_name, strpos($class_name, BS) + 1);
		}
		else {
			list($vendor, $project, $class_sub_path) = explode(BS, $class_name, 3);
			$without = class_exists($vendor . BS . $project . BS . 'Application')
				? $class_sub_path
				: ($project . ($class_sub_path ? (BS . $class_sub_path) : ''));
		}
		return $without;
	}

	//----------------------------------------------------------------------------------------- debug
	/**
	 * Displays debug information
	 *
	 * @param $step      string
	 * @param $path      string
	 * @param $method    string
	 * @param $extension string
	 * @param $what      string
	 */
	static protected function debug(
		string $step, string $path, string $method, string $extension, string $what = 'try'
	) {
		echo "- $what $step : $path" . (($extension === 'html') ? '' : "::$method") . BR;
	}

	//------------------------------------------------------------------------------------------- get
	/**
	 * @noinspection PhpDocMissingThrowsInspection ReflexionException because method exist
	 * @param $base_class   string The base name for the class, ie 'ITRocks\Framework\User'
	 * @param $feature_name string The name of the feature, ie 'list'
	 * @param $suffix       string Class suffix, ie 'Controller', 'View'
	 * @param $extension    string File extension, ie 'php', 'html'
	 * @param $class_form   boolean true to use 'Feature_Class' naming instead of 'featureClass'
	 * @return string[] [$class, $method]
	 */
	static public function get(
		string $base_class, string $feature_name, string $suffix = 'Controller',
		string $extension = 'php', bool $class_form = true
	) : array
	{
		// $feature_class : 'featureName' transformed into 'Feature_Name'
		// $feature_what : is $feature_class or $feature_name depending on $class_name
		$_suffix = $suffix ? ('_' . $suffix) : '';
		$is_php  = ($extension === 'php');

		$application_classes_filter = (new Application_Class_Tree_Filter($base_class))
			->prepare()
			->filter();
		$application_classes         = $application_classes_filter->classes();
		$default_application_classes = $application_classes_filter->defaultApplicationClasses();

		$class_name        = $base_class;
		$ext               = DOT . $extension;
		$feature_class     = Names::methodToClass($feature_name);
		$feature_namespace = self::reservedFeatures($feature_class);
		$feature_directory = strtolower($feature_namespace);
		$feature_what      = $class_form ? $feature_class : $feature_name;
		$method            = 'run';

		// $classes : the controller class name and its parents and traits
		// ['Vendor\Application\Module\Class_Name' => '\Module\Class_Name']
		$classes = self::getClasses($class_name);

		// Looking for specific controller for each application
		$application_class = reset($application_classes);
		do {
			if (isset($default_application_classes[$application_class])) {
				continue;
			}
			$namespace = Namespaces::of($application_class);

			// for the controller class and its parents
			foreach ($classes as $short_class_name) {
				$class_name = $namespace . BS . $short_class_name;
				$path       = strtolower(str_replace(BS, SL, $class_name));
				if (isset($GLOBALS['D'])) {
					static::debug('A1', $path . SL . $feature_what . $_suffix . $ext, 'run', $extension);
				}
				if (
					file_exists($path . SL . $feature_what . $_suffix . $ext)
					&& (!$is_php || class_exists($class_name . BS . $feature_what . $_suffix))
				) {
					$class = $class_name . BS . $feature_what . $_suffix;
					break 2;
				}
				if (isset($GLOBALS['D'])) {
					static::debug(
						'A2',
						$path . SL . $feature_directory . SL . $feature_what . $_suffix . $ext,
						'run',
						$extension
					);
				}
				if (
					file_exists($path . SL . $feature_directory . SL . $feature_what . $_suffix . $ext)
					&& (!$is_php || class_exists($class_name . BS . $feature_namespace . BS . $feature_what . $_suffix))
				) {
					$class = $class_name . BS . $feature_namespace . BS . $feature_what . $_suffix;
					break 2;
				}
				if (isset($GLOBALS['D']) && $suffix) {
					static::debug(
						'A3', $path . SL . $feature_directory . SL . $suffix . $ext, 'run', $extension
					);
				}
				if (
					$suffix
					&& file_exists($path . SL . $feature_directory . SL . $suffix . $ext)
					&& (!$is_php || class_exists($class_name . BS . $feature_namespace . BS . $suffix))
				) {
					$class = $class_name . BS . $feature_namespace . BS . $suffix;
					break 2;
				}
				if (isset($GLOBALS['D'])) {
					static::debug(
						'A4',
						Names::classToPath($class_name) . '_' . $feature_what . $_suffix . $ext,
						'run',
						$extension
					);
				}
				if (
					file_exists(Names::classToPath($class_name) . '_' . $feature_what . $_suffix . $ext)
					&& (!$is_php || class_exists($class_name . '_' . $feature_what . $_suffix))
				) {
					$class = $class_name . '_' . $feature_what . $_suffix;
					break 2;
				}
				if (isset($GLOBALS['D']) && $suffix) {
					static::debug(
						'A5', $path . SL . $suffix . $ext, 'run' . ucfirst($feature_name), $extension
					);
				}
				if (
					$is_php
					&& $suffix
					&& file_exists($path . SL . $suffix . $ext)
					&& class_exists($class_name . BS . $suffix)
					&& method_exists($class_name . BS . $suffix, 'run' . ucfirst($feature_name))
				) {
					$class  = $class_name . BS . $suffix;
					$method = 'run' . ucfirst($feature_name);
					break 2;
				}
				if (isset($GLOBALS['D']) && $suffix) {
					static::debug('A6', $path . SL . $suffix . $ext, 'run', $extension);
				}
				if (
					$ext
					&& $suffix
					&& file_exists($path . SL . $suffix . $ext)
					&& (!$is_php || class_exists($class_name . BS . $suffix))
				) {
					$class = $class_name . BS . $suffix;
					break 2;
				}
			}

		} while ($application_class = next($application_classes));

		// Looking for default controller for each application
		if (empty($class)) {

			// list applications that embed a project class
			// (business class with the same name and path as the project namespace)
			$project_classes = [];
			foreach ($application_classes as $application_class) {
				if (substr_count($application_class, BS) === 2) {
					$project_class      = mParse($application_class, BS, BS);
					$project_class_file = strtolower(str_replace(BS, SL, lLastParse($application_class, BS)))
						. SL . $project_class . '.php';
					if (file_exists($project_class_file)) {
						$project_classes[$application_class] = true;
					}
				}
			}

			$application_class = reset($application_classes);
			do {
				$can_be_project_class = (
					($base_class === lParse($application_class, BS, 2))
					|| !isset($project_classes[$application_class])
				);
				$namespace = Namespaces::of($application_class);
				$path      = strtolower(str_replace(BS, SL, $namespace));
				if (isset($GLOBALS['D']) && $can_be_project_class && $suffix) {
					static::debug(
						'B1', $path . SL . $feature_directory . SL . $suffix . $ext, 'run', $extension
					);
				}
				if (
					$can_be_project_class
					&& $suffix
					&& file_exists($path . SL . $feature_directory . SL . $suffix . $ext)
					&& (!$is_php || class_exists($namespace . BS . $feature_namespace . BS . $suffix))
				) {
					$class = $namespace . BS . $feature_namespace . BS . $suffix;
					break;
				}
				if (isset($GLOBALS['D']) && $can_be_project_class) {
					static::debug(
						'B2',
						$path . SL . $feature_directory . SL . $feature_what . $_suffix . $ext,
						'run',
						$extension
					);
				}
				if (
					$can_be_project_class
					&& file_exists($path . SL . $feature_directory . SL . $feature_what . $_suffix . $ext)
					&& (!$is_php || class_exists($namespace . BS . $feature_namespace . BS . $feature_what . $_suffix))
				) {
					$class = $namespace . BS . $feature_namespace . BS . $feature_what . $_suffix;
					break;
				}
				if (isset($GLOBALS['D']) && $can_be_project_class) {
					static::debug('B3', $path . SL . $feature_what . $_suffix . $ext, 'run', $extension);
				}
				if (
					$can_be_project_class
					&& file_exists($path . SL . $feature_what . $_suffix . $ext)
					&& (!$is_php || class_exists($namespace . BS . $feature_what . $_suffix))
				) {
					$class = $namespace . BS . $feature_what . $_suffix;
					break;
				}
				if (isset($GLOBALS['D']) && $suffix) {
					static::debug(
						'B4',
						$path . SL . 'feature' . SL . $feature_directory . SL . $suffix . $ext,
						'run',
						$extension
					);
				}
				if (
					$suffix
					&& file_exists($path . SL . 'feature' . SL . $feature_directory . SL . $suffix . $ext)
					&& (!$is_php || class_exists($namespace . BS . 'Feature' . BS . $feature_namespace . BS . $suffix))
				) {
					$class = $namespace . BS . 'Feature' . BS . $feature_namespace . BS . $suffix;
					break;
				}
				if (isset($GLOBALS['D'])) {
					static::debug(
						'B5',
						$path . SL . 'feature' . SL . $feature_directory . SL . $feature_what . $_suffix
							. $ext,
						'run',
						$extension
					);
				}
				if (
					file_exists($path . SL . 'feature' . SL . $feature_directory . SL . $feature_what . $_suffix . $ext)
					&& (!$is_php || class_exists($namespace . BS . 'Feature' . BS . $feature_namespace . BS . $feature_what . $_suffix))
				) {
					$class = $namespace . BS . 'Feature' . BS . $feature_namespace . BS . $feature_what . $_suffix;
					break;
				}
				if (isset($GLOBALS['D']) && $suffix) {
					static::debug(
						'B6',
						$path . SL . 'webservice' . SL . $feature_directory . SL . $suffix . $ext,
						'run',
						$extension
					);
				}
				if (
					$suffix
					&& file_exists($path . SL . 'webservice' . SL . $feature_directory . SL . $suffix . $ext)
					&& (!$is_php || class_exists($namespace . BS . 'Webservice' . BS . $feature_namespace . BS . $suffix))
				) {
					$class = $namespace . BS . 'Webservice' . BS . $feature_namespace . BS . $suffix;
					break;
				}
				if (isset($GLOBALS['D'])) {
					static::debug(
						'B7',
						$path . SL . 'webservice' . SL . $feature_directory . SL . $feature_what
							. $_suffix . $ext,
						'run',
						$extension
					);
				}
				if (
					file_exists($path . SL . 'webservice' . SL . $feature_directory . SL . $feature_what . $_suffix . $ext)
					&& (!$is_php || class_exists($namespace . BS . 'Webservice' . BS . $feature_namespace . BS . $feature_what . $_suffix))
				) {
					$class = $namespace . BS . 'Webservice' . BS . $feature_namespace . BS . $feature_what . $_suffix;
					break;
				}
				// next application is the parent one
			} while ($application_class = next($application_classes));

			// Looking for direct feature call, without using any controller
			static $last_controller_class  = '';
			static $last_controller_method = '';
			if (
				empty($class)
				&& (
					!str_contains($suffix, 'View')
					&& ($extension !== 'html')
					&& (
						($last_controller_class  !== $base_class)
						&& ($last_controller_method !== $feature_name)
					)
				)
			) {
				if (str_contains($suffix, 'Controller')) {
					$last_controller_class  = $base_class;
					$last_controller_method = $feature_name;
				}
				if (isset($GLOBALS['D'])) static::debug('C1', $base_class, $feature_name, $extension);
				/** @noinspection PhpUnhandledExceptionInspection method_exists */
				if (
					$is_php
					&& class_exists($base_class)
					&& method_exists($base_class, $feature_name)
					&& (
						!($reflection_method = new Reflection_Method($base_class, $feature_name))->getParameters()
						|| $reflection_method->hasParameter('parameters')
					)
				) {
					$class  = $base_class;
					$method = $feature_name;
				}
			}

			// Looking for default controller for each application
			if (empty($class) && $suffix) {
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
				$application_class = reset($application_classes);
				do {
					$namespace = Namespaces::of($application_class);
					$path      = strtolower(str_replace(BS, SL, $namespace));
					if (isset($GLOBALS['D'])) {
						static::debug(
							'C2', $path . SL . strtolower($sub) . '/Default_' . $suffix . $ext, 'run', $extension
						);
					}
					if (
						file_exists($path . SL . strtolower($sub) . '/Default_' . $suffix . $ext)
						&& (!$is_php || class_exists($namespace . BS . str_replace(SL, BS, $sub) . BS . 'Default_' . $suffix))
					) {
						$class = $namespace . BS . str_replace(SL, BS, $sub) . BS . 'Default_' . $suffix;
						break;
					}
				}
				while ($application_class = next($application_classes));
			}

		}

		$result = [isset($class) ? $class : null, $method];
		if (isset($GLOBALS['D'])) {
			static::debug(strtoupper($suffix ?: $extension), $result[0], $result[1], $extension, 'FOUND');
		}
		return $result;
	}

	//------------------------------------------------------------------------------------ getClasses
	/**
	 * Get classes we can get from, starting from the actual lower descendant
	 *
	 * @noinspection PhpDocMissingThrowsInspection ReflexionException because class exists
	 * @param $class_name string
	 * @return string[] key is the full name of each class, value is it without 'Vendor/Project/
	 */
	static private function getClasses(string $class_name) : array
	{
		$classes = [];

		do {
			$classes[$class_name] = self::classNameWithoutVendorProject($class_name);
			if (class_exists($class_name)) {
				/** @noinspection PhpUnhandledExceptionInspection class exists */
				$reflection_class = new Reflection_Class(Builder::className($class_name));
				// @extends
				$extends_annotations = Extends_Annotation::allOf($reflection_class);
				foreach ($extends_annotations as $extends_annotation) {
					foreach ($extends_annotation->values() as $extends) {
						$classes[$extends] = self::classNameWithoutVendorProject($extends);
					}
				}
				// use and implements (traits, interfaces)
				$classes = array_merge($classes, self::getTraitsRecursive($reflection_class));
				$classes = arrayMergeRecursive($classes, self::getInterfacesRecursive($reflection_class));
				// parent classes
				$class_name = get_parent_class($class_name);
			}
			else {
				$class_name = null;
			}
		} while ($class_name);

		return $classes;
	}

	//------------------------------------------------------------------------ getInterfacesRecursive
	/**
	 * Get interfaces we can get from, starting from the actual class
	 *
	 * @param $class Reflection_Class
	 * @return string[] key is the full name of each interface, value is it without 'Vendor/Project/'
	 */
	static private function getInterfacesRecursive(Reflection_Class $class) : array
	{
		$interfaces = [];
		foreach ($class->getInterfaces() as $interface) {
			$interfaces[$interface->name] = self::classNameWithoutVendorProject($interface->name);
			// don't need to recurse : getInterfaces get them all
			// TODO should restrict to the direct parent interfaces to optimize calls
		}
		return $interfaces;
	}

	//---------------------------------------------------------------------------- getTraitsRecursive
	/**
	 * Get traits we can get from, starting from the actual class / trait
	 *
	 * @param $class Reflection_Class
	 * @return string[] key is the full name of each trait, value is it without 'Vendor/Project/'
	 */
	static private function getTraitsRecursive(Reflection_Class $class) : array
	{
		$traits = [];
		foreach ($class->getTraits() as $trait) {
			$traits[$trait->name] = self::classNameWithoutVendorProject($trait->name);
			$traits               = array_merge($traits, self::getTraitsRecursive($trait));
		}
		return $traits;
	}

	//------------------------------------------------------------------------------ reservedFeatures
	/**
	 * @param $feature string
	 * @return string
	 */
	static protected function reservedFeatures(string $feature) : string
	{
		return in_array($feature, ['List', 'Print'])
			? ($feature . '_')
			: $feature;
	}

}
