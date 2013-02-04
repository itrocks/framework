<?php
namespace SAF\Framework;

/** @noinspection PhpIncludeInspection called from index.php */
require_once "framework/Application.php";

abstract class Namespaces
{

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
		$i = strrpos($model_class_name, "\\");
		if (($i !== false) && (strrpos($class_name, "\\") === false)) {
			$class_name = substr($model_class_name, 0, $i + 1) . $class_name;
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
				foreach (Application::getNamespaces() as $namespace) {
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
