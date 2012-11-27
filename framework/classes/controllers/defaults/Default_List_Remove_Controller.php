<?php
namespace SAF\Framework;

class Default_List_Remove_Controller implements Class_Controller
{

	//------------------------------------------------------------------------------------------- run
	public function run(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$class_name = Namespaces::fullClassName(Set::elementClassNameOf($class_name));
		$properties = $parameters->getObjects();
		foreach ($properties as $property_name) {
			Default_List_Controller_Configuration::current()->removeListProperty(
				$class_name, $property_name
			);
			echo "removed $property_name from $class_name<br>";
		}
	}

}
