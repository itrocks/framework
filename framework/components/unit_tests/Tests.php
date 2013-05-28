<?php
namespace SAF\Framework\Unit_Tests;
use SAF\Framework\Application;
use SAF\Framework\Configuration;
use SAF\Framework\Namespaces;
use SAF\Framework\Reflection_Class;
use SAF\Framework\Reflection_Method;
use SAF\Framework\Unit_Tests\Runnable_Unit_Test;
use SAF\Framework\Unit_Tests\Unit_Test;

/**
 * The tests class enables running of unit test
 */
class Tests
{

	//------------------------------------------------------------------------------------------- run
	public function run()
	{
		$this->runDir(Application::current()->getSourceDirectory() . "/tests");
	}

	//-------------------------------------------------------------------------------------- runClass
	/**
	 * @param $class_name string
	 */
	private function runClass($class_name)
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
			$call_methods = array();
			$methods = Reflection_Class::getInstanceOf($class_name)->getMethods(
				Reflection_Method::IS_PUBLIC
			);
			foreach ($methods as $method) {
				if (substr($method->name, 0, 4) === "test") {
					$call_methods[] = $method->name;
				}
			}
			if ($call_methods) {
				$unit_test->begin();
				foreach ($call_methods as $method) {
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
	private function runFile($file_name)
	{
		include_once $file_name;
		$slash = strrpos($file_name, "/");
		$dot = strrpos($file_name, ".");
		$namespace = Namespaces::of(Configuration::current()->getApplicationClassName());
		$class_name = $namespace . "\\Tests\\" . substr($file_name, $slash + 1, $dot - $slash - 1);
		if (is_subclass_of($class_name, 'SAF\Framework\Unit_Tests\Unit_Test')) {
			$this->runClass($class_name);
		}
	}

}
