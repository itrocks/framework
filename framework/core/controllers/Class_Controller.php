<?php
namespace SAF\Framework;

/**
 * A common controller for all features of a given class
 */
interface Class_Controller extends Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * Run method for the class controller, when no runFeatureName() method was found in it.
	 *
	 * Class controllers must implement this method if you want the controller to work.
	 *
	 * @param $parameters   Controller_Parameters
	 * @param $form         array
	 * @param $files        array
	 * @param $feature_name string
	 * @return mixed
	 */
	public function run(Controller_Parameters $parameters, $form, $files, $feature_name);

}
