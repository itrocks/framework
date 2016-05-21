<?php
namespace SAF\Framework;

use SAF\Framework\AOP\Include_Filter;
use SAF\Framework\Tools\Names;

/**
 * This is the core autoloader : it searches and load PHP scripts containing classes
 */
class Autoloader
{

	//---------------------------------------------------------------------------------------- $files
	/**
	 * Searched filenames or paths
	 *
	 * @var string[]
	 */
	private $files = [];

	//-------------------------------------------------------------------------------------- autoload
	/**
	 * Includes the php file that contains the given class (must contain namespace)
	 *
	 * @param $class_name string class name (with or without namespace)
	 * @return boolean
	 */
	public function autoload($class_name)
	{
		if ($i = strrpos($class_name, '\\')) {
			$namespace = strtolower(str_replace('\\', '/', substr($class_name, 0, $i)));
			$file_name = substr($class_name, $i + 1);
			// 'A\Class' stored into 'a/class/Class.php'
			$file1 = strtolower($namespace . '/' . $file_name) . '/' . $file_name . '.php';
			if (file_exists($file1)) {
				/** @noinspection PhpIncludeInspection */
				$result = include_once(Include_Filter::file($file1));
			}
			// 'A\Class' stored into 'a/Class.php'
			elseif (file_exists($file2 = strtolower($namespace) . '/' . $file_name . '.php')) {
				/** @noinspection PhpIncludeInspection */
				$result = include_once(Include_Filter::file($file2));
			}
			elseif (Builder::isBuilt($class_name)) {
				$file3 = 'cache/compiled/' . str_replace(SL, '-', Names::classToPath($class_name));
				if (file_exists($file3)) {
					/** @noinspection PhpIncludeInspection */
					$result = include_once($file3);
				}
			}
		}
		// 'A_Class' stored into 'A_Class.php'
		elseif (file_exists($class_name . '.php')) {
			$file4 = $class_name . '.php';
			/** @noinspection PhpIncludeInspection */
			$result = include_once(Include_Filter::file($file4));
		}
		// class not found
		if (!isset($result)) {
			$result = false;
		}
		// instantiate plugin
		if ($result && class_exists($class_name, false) && is_a($class_name, Plugin::class, true)) {
			if (Session::current()) {
				Session::current()->plugins->get($class_name);
			}
		}
		$this->files = [];
		if (!$result) {
			if (isset($file1)) $this->files[] = $file1;
			if (isset($file2)) $this->files[] = $file2;
			if (isset($file3)) $this->files[] = $file3;
			if (isset($file4)) $this->files[] = $file4;
		}
		return $result;
	}

	//--------------------------------------------------------------------------------- classNotFound
	/**
	 * This is called when no file containing the class was found
	 *
	 * @param $class_name string
	 * @return boolean false
	 */
	public function classNotFound($class_name)
	{
		$this->files[] = 'vendor';
		if (error_reporting()) {
			trigger_error(
				'Class not found ' . $class_name . ', searched into ' . join(', ', $this->files),
				E_USER_ERROR
			);
		}
		return false;
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * Register autoloader
	 */
	public function register()
	{
		include_once __DIR__ . '/../../vendor/autoload.php';
		spl_autoload_register([$this, 'autoload'], true, true);
		spl_autoload_register([$this, 'classNotFound']);
	}

}
