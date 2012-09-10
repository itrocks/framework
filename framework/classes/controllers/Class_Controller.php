<?php
namespace SAF\Framework;

// TODO doc : what will a class controller be ?
interface Class_Controller extends Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * Default run method for the class controller, when no featureName() method was found in it.
	 *
	 * Class controllers must implement this method if you want the controller to work.
	 *
	 * @param Controller_Parameters $parameters
	 * @param array  $form
	 * @param array  $files
	 * @param string $class_name
	 */
	public function run($parameters, $form, $files, $class_name);

}
