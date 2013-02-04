<?php
namespace SAF\Framework\Tests;
use SAF\Framework\Controller_Parameters;
use SAF\Framework\Feature_Controller;
use SAF\Framework\Reflection_Class;
use SAF\Framework\Reflection_Method;

class Tests_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------ test
	public function run(Controller_Parameters $parameters, $form, $files)
	{
		$dir = dir("tests");
		while ($entry = $dir->read()) {
			if ((substr($entry, -9) == "_Test.php") && ($entry != "Runnable_Unit_Test.php")) {
				$class = __NAMESPACE__ . "\\" . substr($entry, 0, strpos($entry, "."));
				/** @var $unit_test Unit_Test|Runnable_Unit_Test use run method to launch unit tests */
				$unit_test = new $class();
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
			}
		}
		$dir->close();
	}

}
