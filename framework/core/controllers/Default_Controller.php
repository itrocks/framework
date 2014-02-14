<?php
namespace SAF\Framework;

/**
 * The default controller launches a view corresponding to the original controller name
 *
 * It is called if no other specific or default controller is implemented
 */
class Default_Controller implements Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * Default run method for default "view-typed" controller
	 *
	 * Loads data from objects given as parameters, then run the view associated to the first parameter class.
	 * This is called when no other controller was found for the first parameter object.
	 *
	 * @param $parameters   Controller_Parameters
	 * @param $form         array
	 * @param $files        array
	 * @param $class_name   string
	 * @param $feature_name string
	 * @return mixed
	 */
	public function run(Controller_Parameters $parameters, $form, $files, $class_name, $feature_name)
	{
		$parameters->getMainObject($class_name);
		$parameters = $parameters->getObjects();
		return View::run($parameters, $form, $files, $class_name, $feature_name);
	}

}
