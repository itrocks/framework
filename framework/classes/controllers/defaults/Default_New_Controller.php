<?php
namespace SAF\Framework;

class Default_New_Controller extends Default_Output_Controller
{

	//----------------------------------------------------------------------------- getGeneralButtons
	public function getGeneralButtons($object)
	{
		$class = get_class($object);
		return array(
			new Button(
				"Write", View::link($class . "/write"), "write", array(".ifedit", ".submit", "#messages")
			)
		);
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * Default run method for default "form-typed" output view controller
	 *
	 * @param Controller_Parameters $parameters
	 * @param array  $form
	 * @param array  $files
	 * @param string $class_name
	 */
	public function run(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		parent::run($parameters, $form, $files, $class_name);
	}

}
