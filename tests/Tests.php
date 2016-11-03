<?php
namespace ITRocks\Framework;

use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Tests\Runnable;
use ITRocks\Framework\Tests\Test;
use ITRocks\Framework\Tools\Names;

/**
 * The tests class enables running of unit test
 */
class Tests
{

	//--------------------------------------------------------------------------------- $errors_count
	/**
	 * @var integer
	 */
	public $errors_count;

	//----------------------------------------------------------------------------------------- $show
	/**
	 * @var string
	 */
	public $show = Test::ERRORS;

	//---------------------------------------------------------------------------------- $tests_count
	/**
	 * @var integer
	 */
	public $tests_count;

	//------------------------------------------------------------------------------------------- run
	public function run()
	{
		upgradeTimeLimit(120);
		$this->errors_count = 0;
		$this->tests_count  = 0;
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
		$unit_test->show = $this->show;
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
		$this->errors_count += $unit_test->errors_count;
		$this->tests_count  += $unit_test->tests_count;
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

	//----------------------------------------------------------------------------- successPercentage
	/**
	 * @param $with_text boolean
	 * @return integer|string
	 */
	public function successPercentage($with_text)
	{
		$text = $with_text ? '% of success' : '';
		$percent = $this->tests_count
			? (floor(100 * ($this->tests_count - $this->errors_count) / $this->tests_count))
			: '';
		return $with_text ? ($percent . $text) : $percent;
	}

}
