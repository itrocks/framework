<?php
namespace ITRocks\Framework;

use ITRocks\Framework\AOP\Include_Filter;
use ITRocks\Framework\Builder\Class_Builder;
use ITRocks\Framework\Tools\Paths;

/**
 * This is the core autoloader : it searches and load PHP scripts containing classes
 */
class Autoloader
{

	//-------------------------------------------------------------------------------------- autoload
	/**
	 * Includes the php file that contains the given class (must contain namespace)
	 *
	 * @param $class_name string class name (with or without namespace)
	 * @throws Include_Filter\Exception
	 */
	public function autoload(string $class_name) : void
	{
		$this->tryToLoad($class_name);
	}

	//----------------------------------------------------------------------------------- getFilePath
	/**
	 * Returns the existing source file name for a class
	 *
	 * @param $class_name  string
	 * @param $path_prefix string
	 * @return boolean|string the matching file path, relative to the project root, false if not found
	 */
	public static function getFilePath(string $class_name, string $path_prefix = '') : bool|string
	{
		if (($path_prefix !== '') && !str_ends_with($path_prefix, SL)) {
			$path_prefix .= SL;
		}
		if ($i = strrpos($class_name, BS)) {
			$namespace = $path_prefix . strtolower(str_replace(BS, SL, substr($class_name, 0, $i)));
			$short_class_name = substr($class_name, $i + 1);
			// 'A\Class' stored into 'a/class/Class.php'
			$file = strtolower($namespace . SL . $short_class_name) . SL . $short_class_name . '.php';
			if (file_exists(Paths::$project_root . SL . $file)) {
				return $file;
			}
			// 'A\Class' stored into 'a/Class.php'
			else {
				$file = strtolower($namespace) . SL . $short_class_name . '.php';
				if (file_exists(Paths::$project_root . SL . $file)) {
					return $file;
				}
			}
		}
		// 'A_Class' stored into 'A_Class.php'
		else {
			$file = $path_prefix . $class_name . '.php';
			if (file_exists(Paths::$project_root . SL . $file)) {
				return $file;
			}
		}
		return false;
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * Register autoloader
	 */
	public function register() : void
	{
		include_once __DIR__ . '/../../vendor/autoload.php';
		spl_autoload_register([$this, 'autoload'], true, true);
	}

	//------------------------------------------------------------------------------------- tryToLoad
	/**
	 * @param $class_name string class name (with or without namespace)
	 * @return boolean|integer
	 * @throws Include_Filter\Exception
	 */
	public function tryToLoad(string $class_name) : bool|int
	{
		$file_name = self::getFilePath($class_name);
		if ($file_name !== false) {
			$result = include_once(Include_Filter::file($file_name));
		}
		if ((!isset($result) || !$result) && Class_Builder::isBuilt($class_name)) {
			$built_file_name = PHP\Compiler::classToCacheFilePath($class_name);
			if (file_exists(Paths::$project_root . SL . $built_file_name)) {
				$result = include_once(Paths::$project_root . SL . $built_file_name);
			}
		}
		// class not found
		if (!isset($result)) {
			$result = false;
		}
		return $result;
	}

}
