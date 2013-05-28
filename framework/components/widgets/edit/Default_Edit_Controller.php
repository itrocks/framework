<?php
namespace SAF\Framework;

/**
 * The default edit controller, when no edit controller is set for the class
 */
class Default_Edit_Controller extends Default_Output_Controller
{

	//----------------------------------------------------------------------------- getGeneralButtons
	/**
	 * @param $object object|string object or class name
	 * @param $parameters string[] parameters
	 * @return Button[]
	 */
	protected function getGeneralButtons($object, $parameters)
	{
		$fill_combo = isset($parameters["fill_combo"])
			? "?fill_combo=" . $parameters["fill_combo"] : "";
		return Button::newCollection(array(
			array("Cancel",
				View::link($object, "output"),
				"cancel",
				array(Color::of("close"), "#main")
			),
			array("Write",
				View::link($object, "write") . $fill_combo,
				"write",
				array(Color::of("green"), "#messages", ".submit"))
		));
	}

	//----------------------------------------------------------------------------- getViewParameters
	/**
	 * @param $parameters Controller_Parameters
	 * @param $form       array
	 * @param $class_name string
	 * @return mixed[]
	 */
	protected function getViewParameters(Controller_Parameters $parameters, $form, $class_name)
	{
		$parameters = parent::getViewParameters($parameters, $form, $class_name);
		$parameters["feature"] = "edit";
		$parameters["template_mode"] = "edit";
		return $parameters;
	}

}
