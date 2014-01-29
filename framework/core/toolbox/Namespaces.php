<?php
namespace SAF\Framework;

/**
 * A functions library to deal with class names and namespaces
 */
abstract class Namespaces
{

	//--------------------------------------------------------------------------------- checkFilePath
	/**
	 * Check class file path for namespace
	 *
	 * @param $class_name string
	 * @param $file_path  string
	 * @return boolean
	 */
	public static function checkFilePath($class_name, $file_path)
	{
		if ($class_name[0] === "\\") {
			$class_name = substr($class_name, 1);
		}
		$file_space = explode("/", substr($file_path, strlen(getcwd()) + 1));
		$name_space = explode("\\", strtolower($class_name));
		// remove main part of the namespace, and the class name too
		array_pop($name_space);
		array_shift($name_space);
		$file = 0;
		foreach ($name_space as $name) {
			while (($file < count($file_space)) && ($file_space[$file] != $name)) {
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
	 * @param $class_name string
	 * @param $model_class_name string
	 * @return string
	 */
	public static function defaultFullClassName($class_name, $model_class_name)
	{
		if (strpos($class_name, "\\") === false) {
			if (($i = strrpos($model_class_name, "\\")) !== false) {
				$class_name = substr($model_class_name, 0, $i + 1) . $class_name;
			}
		}
		return $class_name;
	}

	//--------------------------------------------------------------------------------- fullClassName
	/**
	 * Get full class name (with namespace) for a given class name (with or without namespace)
	 *
	 * @param $class_name string
	 * @return string
	 */
	public static function fullClassName($class_name)
	{
		$full_class_name = $class_name;
		if (strpos($class_name, "\\") === false) {
			static $cache = array();
			if (isset($cache[$class_name])) {
				$full_class_name = $cache[$class_name];
			}
			else {
				foreach (Application::getCurrentNamespaces() as $namespace) {
					$full_class_name = $namespace . "\\" . $class_name;
					if (@class_exists($full_class_name) || @interface_exists($full_class_name)) {
						$cache[$class_name] = $full_class_name;
						break;
					}
				}
				if (!isset($cache[$class_name])) {
					$full_class_name = $class_name;
				}
			}
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
	public static function isFullClassName($class_name)
	{
		return strpos($class_name, "\\") !== false;
	}

	//------------------------------------------------------------------------------ isShortClassName
	/**
	 * Returns true if $class_name is a short class name, without namespace
	 *
	 * @param $class_name string
	 * @return boolean
	 */
	public static function isShortClassName($class_name)
	{
		return strpos($class_name, "\\") === false;
	}

	//-------------------------------------------------------------------------------------------- of
	/**
	 * Returns the namespace from a class name, or an empty string if the class is in the global scope
	 *
	 * @param $class_name
	 * @return string
	 */
	public static function of($class_name)
	{
		if (is_object($class_name)) {
			$class_name = get_class($class_name);
		}
		if ($i = strrpos($class_name, "\\")) {
			return substr($class_name, 0, $i);
		}
		else {
			return "";
		}
	}

	//-------------------------------------------------------------------------------- shortClassName
	/**
	 * Get the short class name (without it's namespace) for a given class name
	 * (with or without namespace)
	 *
	 * @param $class_name string
	 * @return string
	 */
	public static function shortClassName($class_name)
	{
		$i = strrpos($class_name, "\\");
		if ($i !== false) {
			$class_name = substr($class_name, $i + 1);
		}
		return $class_name;
	}

}
