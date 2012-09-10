<?php
namespace SAF\Framework;

class Default_Controller implements Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * Default run method for default "view-typed" controller
	 *
	 * Loads data from objects given as parameters, then run the view associated to the first parameter class.
	 * This is called when no other controller was found for the first parameter object.
	 *
	 * @param Controller_Parameters $parameters
	 * @param array  $form
	 * @param array  $files
	 * @param string $class_name
	 * @param string $feature_name
	 */
	public function run($parameters, $form, $files, $class_name, $feature_name)
	{
		$parameters = $parameters->getObjects();
		View::run($parameters, $form, $files, $class_name, $feature_name);
	}

}
