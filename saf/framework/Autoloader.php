<?php
namespace SAF\Framework;

use SAF\Framework\AOP\Include_Filter;
use SAF\Framework\Tools\Names;

/**
 * This is the core autoloader : it searches and load PHP scripts containing classes
 */
class Autoloader
{

	//-------------------------------------------------------------------------------------- $loading
	/**
	 * The list of currently loading classes
	 *
	 * @var string[] key and value both are the name of the currently loading class
	 */
	private $loading = [];

	//-------------------------------------------------------------------------------------- autoLoad
	/**
	 * Includes the php file that contains the given class (must contain namespace)
	 *
	 * @param $class_name string class name (with or without namespace)
	 * @return boolean
	 */
	public function autoload($class_name)
	{
		if (!isset($this->loading[$class_name])) {
			$this->loading[$class_name] = $class_name;
			if (!isset($this->loading[Builder::class])) {
				$builder_was_enabled        = Builder::current()->enabled;
				Builder::current()->enabled = false;
			}
			if ($i = strrpos($class_name, '\\')) {
				$namespace = strtolower(str_replace('\\', '/', substr($class_name, 0, $i)));
				$file_name = substr($class_name, $i + 1);
				// 'A\Class' stored into 'a/class/Class.php'
				if (
					file_exists($file1 = strtolower($namespace . '/' . $file_name) . '/' . $file_name . '.php')
				) {
					/** @noinspection PhpIncludeInspection */
					$result = include_once(Include_Filter::file($file1));
				}
				// 'A\Class' stored into 'a/Class.php'
				elseif (file_exists($file2 = strtolower($namespace) . '/' . $file_name . '.php')) {
					/** @noinspection PhpIncludeInspection */
					$result = include_once(Include_Filter::file($file2));
				}
				else {
					if (Builder::isBuilt($class_name)) {
						$file = 'cache/compiled/' . str_replace(SL, '-', Names::classToPath($class_name));
						if (file_exists($file)) {
							/** @noinspection PhpIncludeInspection */
							$result = include_once($file);
						}
					}
					if (!isset($result)) {
						if (error_reporting()) {
							trigger_error(
								'Class not found ' . $class_name . ', should be into ' . $file1 . ' or ' . $file2,
								E_USER_ERROR
							);
						}
						$result = false;
					}
				}
			}
			// 'A_Class' stored into 'A_Class.php'
			else {
				/** @noinspection PhpIncludeInspection */
				$result = include_once(Include_Filter::file($class_name . '.php'));
			}
			if (isset($builder_was_enabled)) {
				Builder::current()->enabled = $builder_was_enabled;
			}
			// instantiate plugin
			if ($result && class_exists($class_name, false) && is_a($class_name, Plugin::class, true)) {
				if (Session::current()) {
					Session::current()->plugins->get($class_name);
				}
			}
			unset($this->loading[$class_name]);
		}
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * Register autoloader
	 */
	public function register()
	{
		spl_autoload_register([$this, 'autoload']);
	}

}
