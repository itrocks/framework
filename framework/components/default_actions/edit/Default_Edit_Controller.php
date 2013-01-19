<?php
namespace SAF\Framework;

class Default_Edit_Controller extends Default_Output_Controller
{

	//----------------------------------------------------------------------------- getGeneralButtons
	protected function getGeneralButtons($object)
	{
		return array(
			new Button(
				"Write", View::link($object, "write"), "write", array(".submit", "#messages")
			)
		);
	}

	//----------------------------------------------------------------------------- getViewParameters
	protected function getViewParameters(Controller_Parameters $parameters, $class_name)
	{
		$parameters = parent::getViewParameters($parameters, $class_name);
		$parameters["template_mode"] = "edit";
		return $parameters;
	}

}
