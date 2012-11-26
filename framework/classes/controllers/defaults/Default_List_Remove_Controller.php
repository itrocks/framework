<?php
namespace SAF\Framework;

class Default_List_Remove_Controller implements Class_Controller
{

	//------------------------------------------------------------------------------------------- run
	public function run(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$properties = $parameters->getObjects();
		foreach ($properties as $property_name) {
			echo "remove $property_name from $class_name<br>";
		}
	}

}
