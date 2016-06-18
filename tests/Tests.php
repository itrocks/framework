<?php
namespace SAF\Framework;

use SAF\Framework\Reflection\Reflection_Class;
use SAF\Framework\Tests\Runnable;
use SAF\Framework\Tests\Test;
use SAF\Framework\Tools\Names;

/**
 * The tests class enables running of unit test
 */
class Tests
{

	//------------------------------------------------------------------------------------------- run
	public function run()
	{
		foreach (Application::current()->include_path->getSourceDirectories() as $directory_name) {
			$this->runDir($directory_name);
		}
	}

	//-------------------------------------------------------------------------------------- runClass
	/**
	 * @param $class_name   string
	 * @param $method_name string
	 */
	public function runClass($class_name, $method_name = null)
	{
		/** @var $unit_test Test|Runnable */
		$unit_test = new $class_name();
		if ($unit_test instanceof Runnable) {
			$unit_test->begin();
			$unit_test->run();
			$unit_test->end();
		}
		else {
			// automatically call each test* public method
			if (empty($method_name)) {
				$call_methods = [];
				$methods = (new Reflection_Class($class_name))->getMethods();
				foreach ($methods as $method) {
					if ($method->isPublic() && (substr($method->name, 0, 4) === 'test')) {
						$call_methods[] = $method->name;
					}
				}
			}
			else {
				$call_methods = [$method_name];
			}
			if ($call_methods) {
				$unit_test->begin();
				foreach ($call_methods as $method) {
					$unit_test->start_time = microtime(true);
					call_user_func([$unit_test, $method]);
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
		if (!file_exists($directory_name . '/exclude')) {
			$dir = dir($directory_name);
			while ($entry = $dir->read()) if ($entry[0] != DOT) {
				$full_entry = $directory_name . SL . $entry;
				if (is_dir($full_entry)) {
					$this->runDir($full_entry);
				}
				elseif (ctype_upper($entry[0]) && (substr($entry, -4) === '.php')) {
					$this->runFile($full_entry);
				}
			}
			$dir->close();
		}
	}

	//--------------------------------------------------------------------------------------- runFile
	/**
	 * @param $file_name string
	 */
	public function runFile($file_name)
	{
		// Accept both Class_Name/Class_Name and Class_Name namespaces
		$file_parts = explode(SL, substr($file_name, 0, -4));
		if (
			(count($file_parts) > 2)
			&& (strtolower(end($file_parts)) == strtolower(prev($file_parts)))
		) {
			$long_class_name = Names::pathToClass(join(SL, $file_parts));
			unset($file_parts[key($file_parts)]);
		}
		$short_class_name = Names::pathToClass(join(SL, $file_parts));
		if (
			is_subclass_of($class_name = $short_class_name, Test::class)
			|| (isset($long_class_name) && is_subclass_of($class_name = $long_class_name, Test::class))
		) {
			$this->runClass($class_name);
		}
	}

}
