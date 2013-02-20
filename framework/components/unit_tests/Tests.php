<?php
namespace SAF\Framework\Unit_Tests;

class Tests
{

	//------------------------------------------------------------------------------------------- run
	public function run()
	{
		$this->runDir("tests");
	}

	//-------------------------------------------------------------------------------------- runClass
	private function runClass($class_name)
	{
		/** @var $unit_test Unit_Test */
		echo "run class $class_name<br>";
		/**
		$unit_test = new $class_name();
		if ($unit_test instanceof Runnable_Unit_Test) {
			$unit_test->begin();
			$unit_test->run();
			$unit_test->end();
		}
		else {
			// automatically call each test* public method
			$call_methods = array();
			$methods = Reflection_Class::getInstanceOf($class)->getMethods(
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
		*/
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
		$class_name = substr($file_name, $slash + 1, $dot - $slash - 1);
		if (is_subclass_of($class_name, 'SAF\Framework\Unit_Test')) {
			$this->runClass($class_name);
		}
	}

}
