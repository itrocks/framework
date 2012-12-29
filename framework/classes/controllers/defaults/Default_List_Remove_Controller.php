<?php
namespace SAF\Framework;

class Default_List_Remove_Controller implements Class_Controller
{

	//------------------------------------------------------------------------------------------- run
	public function run(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$full_class_name = Namespaces::fullClassName(Set::elementClassNameOf($class_name));
		$properties = $parameters->getObjects();
		foreach ($properties as $key => $property_name) {
			if (is_numeric($key)) {
			}
		}
		(new Default_List_Controller())->run(new Controller_Parameters(), array(), array(), $class_name);
	}

}
