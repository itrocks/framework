<?php
namespace SAF\Framework;

class Default_Remove_Controller implements Default_Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	public function run(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$parameters = $parameters->getObjects();
		$parameters["class_name"] = $parameters[0];
		$parameters["feature"] = $parameters[1];
		array_unshift($parameters, Object_Builder::current()->newInstance($class_name));
		View::run($parameters, $form, $files, $class_name, "remove_unavailable");
	}

}
