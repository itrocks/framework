<?php
namespace ITRocks\Framework\Tools;

use ITRocks\Framework\Application;
use ITRocks\Framework\Builder\Class_Builder;
use ITRocks\Framework\Router;

/**
 * A functions library to deal with class names and namespaces
 */
abstract class Namespaces
{

	//--------------------------------------------------------------------------------------- $router
	/**
	 * @var Router
	 */
	public static Router $router;

	//-------------------------------------------------------------------------- applicationNamespace
	/**
	 * Gets the namespace of the application where the class is stored
	 *
	 * @param $class_name string
	 * @return ?string
	 */
	public static function applicationNamespace(string $class_name) : ?string
	{
		$application_namespaces = Application::current()->getNamespaces();
		// priority to long namespaces, before short namespaces
		// eg Vendor\Project before Vendor, if a core project exists
		sort($application_namespaces);
		foreach (array_reverse($application_namespaces) as $application_namespace) {
			if (
				str_starts_with($class_name, $application_namespace . BS)
				|| ($class_name === $application_namespace)
			) {
				return $application_namespace;
			}
		}
		return null;
	}

	//--------------------------------------------------------------------------------- checkFilePath
	/**
	 * Check class file path for namespace
	 *
	 * @param $class_name string
	 * @param $file_path  string
	 * @return boolean
	 * @todo regexp to make this faster
	 */
	public static function checkFilePath(string $class_name, string $file_path) : bool
	{
		$file_space = explode(SL, substr($file_path, strlen(getcwd()) + 1));
		$name_space = explode(BS, strtolower($class_name));
		// remove main part of the namespace, and the class name too
		array_pop($name_space);
		array_shift($name_space);
		$file = 0;
		foreach ($name_space as $name) {
			while (($file < count($file_space)) && ($file_space[$file] !== $name)) {
				$file ++;
			}
			$file ++;
		}
		return $file < count($file_space);
	}

	//-------------------------------------------------------------------------- defaultFullClassName
	/**
	 * Get full class name (with namespace) for a given class name (with or without namespace)
	 * If source $class_name has no namespace, namespace will be taken from $model_class_name
	 * If $model_class_name has no namespace too, $class_name will be kept without namespace
	 *
	 * @param $class_name       string
	 * @param $model_class_name string
	 * @return string
	 */
	public static function defaultFullClassName(string $class_name, string $model_class_name) : string
	{
		if (!str_contains($class_name, BS)) {
			if (($i = strrpos($model_class_name, BS)) !== false) {
				$class_name = substr($model_class_name, 0, $i + 1) . $class_name;
			}
		}
		return $class_name;
	}

	//--------------------------------------------------------------------------------- fullClassName
	/**
	 * Get full class name (with namespace) for a given class name (with or without namespace)
	 *
	 * TODO HIGH will be removed when not used anymore (which would be the best for everyone)
	 *
	 * @param $short_class_name string
	 * @param $error            boolean
	 * @return string
	 */
	public static function fullClassName(string $short_class_name, bool $error = true) : string
	{
		if (!$short_class_name) {
			trigger_error('Missing class name', E_USER_ERROR);
		}
		if (!str_contains($short_class_name, BS)) {
			$full_class_name = isset(self::$router)
				? self::$router->getFullClassName($short_class_name)
				: '';
			if (!$full_class_name) {
				if ($error) {
					trigger_error('Full class name not found for ' . $short_class_name, E_USER_ERROR);
				}
				else {
					$full_class_name = $short_class_name;
				}
			}
		}
		else {
			$full_class_name = $short_class_name;
		}
		return $full_class_name;
	}

	//------------------------------------------------------------------------------- isFullClassName
	/**
	 * Returns true if $class_name is a full class name, with namespace
	 *
	 * @param $class_name string
	 * @return boolean
	 */
	public static function isFullClassName(string $class_name) : bool
	{
		return str_contains($class_name, BS);
	}

	//------------------------------------------------------------------------------ isShortClassName
	/**
	 * Returns true if $class_name is a short class name, without namespace
	 *
	 * @param $class_name string
	 * @return boolean
	 */
	public static function isShortClassName(string $class_name) : bool
	{
		return !str_contains($class_name, BS);
	}

	//---------------------------------------------------------------------------------------- module
	/**
	 * @param $class_name string
	 * @param $level      integer Module level : 1 is the first level, under the project name
	 * @return string
	 */
	public static function module(string $class_name, int $level = 1) : string
	{
		if (Class_Builder::isBuilt($class_name)) {
			$level ++;
		}
		return mParse($class_name, BS, BS, $level + 1);
	}

	//-------------------------------------------------------------------------------------------- of
	/**
	 * Returns the namespace from a class name, or an empty string if the class is in the global scope
	 *
	 * @param $class_name object|string
	 * @return string
	 */
	public static function of(object|string $class_name) : string
	{
		if (is_object($class_name)) {
			$class_name = get_class($class_name);
		}
		if ($i = strrpos($class_name, BS)) {
			return substr($class_name, 0, $i);
		}
		return '';
	}

	//--------------------------------------------------------------------------------------- project
	/**
	 * @param $class_name string
	 * @return string
	 */
	public static function project(string $class_name) : string
	{
		return mParse($class_name, BS, BS);
	}

	//------------------------------------------------------------------------------- resolveFilePath
	/**
	 * Resolve file path using the namespace method
	 * Slower than stream_resolve_include_path() but checks namespace
	 *
	 * @param $class_name string
	 * @return ?string
	 */
	public static function resolveFilePath(string $class_name) : ?string
	{
		$namespace = substr($class_name, strpos($class_name, BS) + 1);
		$short_class_name = substr($class_name, strrpos($class_name, BS) + 1);
		$namespace = substr($namespace, 0, strrpos($namespace, BS));
		$include_path = get_include_path();
		$sl1 = '(?:[^:]*/)*';
		$sl2 = '(?:/[^:]*)*';
		$preg = '%(' . $sl1 . strtolower(str_replace(BS, SL . $sl1, $namespace)) . $sl2 . ')%';
		preg_match_all($preg, $include_path, $match);
		foreach ($match[1] as $file_path) {
			if (file_exists($file_path . SL . $short_class_name . '.php')) {
				return $file_path . SL . $short_class_name . '.php';
			}
		}
		return null;
	}

	//-------------------------------------------------------------------------------- shortClassName
	/**
	 * Get the short class name (without its namespace) for a given class name
	 * (with or without namespace)
	 *
	 * @param $class_name string
	 * @return string
	 */
	public static function shortClassName(string $class_name) : string
	{
		$i = strrpos($class_name, BS);
		if ($i !== false) {
			$class_name = substr($class_name, $i + 1);
		}
		return $class_name;
	}

	//----------------------------------------------------------------------------------------- split
	/**
	 * Splits a 'Full\Class\Name' to get its 'Full\Class' namespace and its short class name 'Name'
	 *
	 * @param $class_name string
	 * @return string[] [$namespace, $short_class_name]
	 */
	public static function split(string $class_name) : array
	{
		$i = strrpos($class_name, BS);
		return ($i === false)
			? ['', $class_name]
			: [substr($class_name, 0, $i), substr($class_name, $i + 1)];
	}

	//---------------------------------------------------------------------------------------- vendor
	/**
	 * @param $class_name string
	 * @return string
	 */
	public static function vendor(string $class_name) : string
	{
		return lParse($class_name, BS);
	}

}
