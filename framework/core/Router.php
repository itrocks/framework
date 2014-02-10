<?php
namespace SAF\Framework;

use SAF\Plugins;

/**
 * Automatic routing class
 */
class Router implements Plugins\Configurable, IAutoloader
{

	//-------------------------------------------------------------------------------------- $changes
	/**
	 * @var boolean
	 */
	public $changes = false;

	//---------------------------------------------------------------------------------- $class_paths
	/**
	 * @var string[] key is full class name, value is file path
	 */
	public $class_paths = array();

	//-------------------------------------------------------------------------------------- $exclude
	/**
	 * @var string
	 */
	public $exclude = '';

	//----------------------------------------------------------------------------- $full_class_names
	/**
	 * @var string[] key is short class name, value is full class name
	 */
	public $full_class_names = array();

	//---------------------------------------------------------------------------------- $routes_file
	/**
	 * @var string
	 */
	public $routes_file;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Open routes cache file
	 *
	 * @param $configuration array
	 */
	public function __construct($configuration = array())
	{
		if (isset($configuration['exclude'])) {
			$this->exclude = '(' . join('|', $configuration['exclude']) . ')';
		}

		$this->routes_file = getcwd() . '/routes.php';
		if (file_exists($this->routes_file)) {
			/** @noinspection PhpIncludeInspection */
			include $this->routes_file;
		}

		Namespaces::$router = $this;
		spl_autoload_register(array($this, 'autoload'));
	}

	//------------------------------------------------------------------------------------ __destruct
	public function __destruct()
	{
		if ($this->changes) {
			ksort($this->full_class_names);
			ksort($this->class_paths);
			file_put_contents(
				$this->routes_file,
				'<?php

$this->full_class_names = ' . var_export($this->full_class_names, true) . ';

$this->class_paths = ' . var_export($this->class_paths, true) . ';
'
			);
		}
	}

	//---------------------------------------------------------------------------------- addClassPath
	/**
	 * @param $class_name string
	 * @return string
	 */
	private function addClassPath($class_name)
	{
		$result = '';
		foreach ($this->filesFor(Namespaces::shortClassName($class_name)) as $file_name) {
			$in_class_name = $this->fileToClassName($file_name);
			if ($in_class_name) {
				$this->changes = true;
				$this->class_paths[$in_class_name] = $file_name;
				if ($in_class_name == $class_name) {
					$result = $file_name;
				}
			}
		}
		return $result;
	}

	//------------------------------------------------------------------------------ addFullClassName
	/**
	 * @param $short_class_name string
	 * @return string
	 */
	private function addFullClassName($short_class_name)
	{
		$result = '';
		foreach ($this->filesFor($short_class_name) as $file_name) {
			$in_class_name = $this->fileToClassName($file_name);
			if ($in_class_name) {
				$this->changes = true;
				if (!$result) {
					$this->full_class_names[$short_class_name] = $result = $in_class_name;
				}
				$this->class_paths[$in_class_name] = $file_name;
			}
		}
		return $result;
	}

	//-------------------------------------------------------------------------------------- autoload
	/**
	 * @param $class_name string
	 * @return string
	 */
	public function autoload($class_name)
	{
		$file_path = $this->getClassPath($class_name);
		if ($file_path) {
			/** @noinspection PhpIncludeInspection */
			include_once $file_path;
			if (is_a($class_name, Plugins\Plugin::class, true)) {
				Session::current()->plugins->get($class_name);
			}
		}
		return $file_path;
	}

	//-------------------------------------------------------------------------------------- filesFor
	/**
	 * @param $short_class_name string
	 * @return string[] files path
	 */
	private function filesFor($short_class_name)
	{
		$result = array();
		$match = null;
		foreach (explode(':', get_include_path()) as $path) {
			if ($this->exclude) preg_match($this->exclude, $path, $match);
			if (!$match) {
				if (file_exists($path . '/' . $short_class_name . '.php')) {
					$result[] = $path . '/' . $short_class_name . '.php';
				}
			}
		}
		return $result;
	}

	//------------------------------------------------------------------------------- fileToClassName
	/**
	 * @param $file_name string
	 * @return string
	 */
	private function fileToClassName($file_name)
	{
		$buffer = file_get_contents($file_name);
		$expr = '%\n\s*(?:namespace\s+)([\w\\\\]+)%s';
		preg_match($expr, $buffer, $match);
		$in_namespace = $match ? $match[1] : '';
		$expr = '%\n\s*(?:abstract\s+)?(?:class|interface|trait)\s+(\w+)%s';
		preg_match($expr, $buffer, $match);
		$class_name = $match
			? ($in_namespace ? ($in_namespace . '\\' . $match[1]) : $match[1])
			: null;
		return $class_name;
	}

	//------------------------------------------------------------------------------ getFullClassName
	/**
	 * Checks, searches, and gets the main full class name for a short class name
	 *
	 * @param $short_class_name string
	 * @return string
	 */
	public function getFullClassName($short_class_name)
	{
		if (strpos($short_class_name, '\\')) {
			trigger_error('Full class name given', E_USER_ERROR);
		}
		if (isset($this->full_class_names[$short_class_name])) {
			$class_name = $this->full_class_names[$short_class_name];
			if (!class_exists($class_name)) {
				$class_name = $this->addFullClassName($short_class_name);
			}
			return $class_name;
		}
		else {
			return $this->addFullClassName($short_class_name);
		}
	}

	//---------------------------------------------------------------------------------- getClassPath
	/**
	 * Checks, searches, and gets the file path for a class name
	 *
	 * @param $class_name string
	 * @return string
	 */
	public function getClassPath($class_name)
	{
		if (isset($this->class_paths[$class_name])) {
			$class_path = $this->class_paths[$class_name];
			/** @noinspection PhpIncludeInspection */
			if (!@include_once($class_path)) {
				$class_path = $this->addClassPath($class_name);
			}
			elseif (!class_exists($class_name)) {
				$class_path = $this->addClassPath($class_name);
			}
			return $class_path;
		}
		else {
			return $this->addClassPath($class_name);
		}
	}

}
