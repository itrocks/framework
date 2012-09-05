<?php
namespace SAF\Framework\Tests;
use SAF\Framework\Reflection_Class;
use SAF\Framework\Reflection_Method;

class Tests_Controller
{

	//------------------------------------------------------------------------------------------ test
	public function run()
	{
		$dir = dir("Framework/tests");
		while ($entry = $dir->read()) {
			if (substr($entry, -9) == "_Test.php") {
				$class = lParse($entry, ".");
				if (method_exists($class, "run")) {
					// use run method to launch unit tests
					$object = new $class();
					$object->begin();
					$object->run();
					$object->end();
				} else {
					// automatically call each test* public method
					$call_methods = array();
					$methods = Reflection_Class::getInstanceOf($class)->getMethods(Reflection_Method::IS_PUBLIC);
					foreach ($methods as $method) {
						if (substr($method->name, 0, 4) === "test") {
							$call_methods[] = $method->name;
						}
					}
					if ($call_methods) {
						$object = new $class();
						$object->begin();
						foreach ($call_methods as $method) {
							$object->$method();
						}
						$object->end();
					}
				}
			}
		}
		$dir->close();
	}

}
