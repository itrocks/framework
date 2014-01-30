<?php
namespace SAF\Framework\Unit_Tests;

use SAF\Framework\Application;
use SAF\Framework\Namespaces;
use SAF\Framework\Reflection_Class;
use SAF\Framework\Reflection_Method;

/**
 * The tests class enables running of unit test
 */
class Tests
{

	//------------------------------------------------------------------------------------------- run
	public function run()
	{
		$this->runDir(Application::current()->path->getSourceDirectory() . "/tests");
	}

	//-------------------------------------------------------------------------------------- runClass
	/**
	 * @param $class_name   string
	 * @param $method_name string
	 */
	public function runClass($class_name, $method_name = null)
	{
		/** @var $unit_test Runnable_Unit_Test|Unit_Test */
		$unit_test = new $class_name();
		if ($unit_test instanceof Runnable_Unit_Test) {
			$unit_test->begin();
			$unit_test->run();
			$unit_test->end();
		}
		else {
			// automatically call each test* public method
			if (empty($method_name)) {
				$call_methods = array();
				$methods = (new Reflection_Class($class_name))->getMethods(
					Reflection_Method::IS_PUBLIC
				);
				foreach ($methods as $method) {
					if (substr($method->name, 0, 4) === "test") {
						$call_methods[] = $method->name;
					}
				}
			}
			else {
				$call_methods = array($method_name);
			}
			if ($call_methods) {
				$unit_test->begin();
				foreach ($call_methods as $method) {
					$unit_test->start_time = microtime(true);
					call_user_func(array($unit_test, $method));
				}
				$unit_test->end();
			}
		}
	}

	//---------------------------------------------------------------------------------------- runDir
	/**
	 * @param $directory_name string
	 */
	private function runDir($directory_name)
	{
		$dir = dir($directory_name);
		while ($entry = $dir->read()) if ($entry[0] != ".") {
			$full_entry = $directory_name . "/" . $entry;
			if (is_file($full_entry)) {
				$this->runFile($full_entry);
			}
			elseif (is_dir($full_entry)) {
				$this->runDir($full_entry);
			}
		}
		$dir->close();
	}

	//--------------------------------------------------------------------------------------- runFile
	/**
	 * @param $file_name string
	 */
	public function runFile($file_name)
	{
		include_once $file_name;
		$slash = strrpos($file_name, "/");
		$dot = strrpos($file_name, ".");
		$namespace = Namespaces::of(get_class(Application::current()));
		$class_name = $namespace . "\\Tests\\" . substr($file_name, $slash + 1, $dot - $slash - 1);
		if (is_subclass_of($class_name, 'SAF\Framework\Unit_Tests\Unit_Test')) {
			$this->runClass($class_name);
		}
	}

}
