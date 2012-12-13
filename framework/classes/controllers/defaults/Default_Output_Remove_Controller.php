<?php
namespace SAF\Framework;

class Default_Output_Remove_Controller implements Class_Controller
{

	//------------------------------------------------------------------------------------------- run
	public function run(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$full_class_name = Namespaces::fullClassName(Set::elementClassNameOf($class_name));
		$properties = $parameters->getObjects();
		foreach ($properties as $key => $property_name) {
			if (is_numeric($key)) {
				Default_Output_Controller_Configuration::current()->removeClassProperty(
					$full_class_name, $property_name
				);
				unset($properties[$key]);
			}
		}
		(new Default_Output_Controller())->run($properties, array(), array(), $class_name);
	}

}
