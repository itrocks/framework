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

	//------------------------------------------------------------------------------------------- run
	/**
	 * Default run method for default form edit view controller
	 *
	 * @param Controller_Parameters $parameters
	 * @param array  $form
	 * @param array  $files
	 * @param string $class_name
	 */
	public function run(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$parameters = $this->getViewParameters($parameters, $class_name);
		$parameters["template_mode"] = "edit";
		View::run($parameters, $form, $files, $class_name, "output");
	}

}
