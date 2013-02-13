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
	 * @param $parameters Controller_Parameters
	 * @param $form array
	 * @param $files array
	 * @param $class_name string
	 * @return mixed
	 */
	public function run(Controller_Parameters $parameters, $form, $files, $class_name);

}
