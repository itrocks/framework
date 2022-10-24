<?php
namespace ITRocks\Framework\Controller;

use ITRocks\Framework\Controller;

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
	 * @param $parameters   Parameters
	 * @param $form         array
	 * @param $files        array[]
	 * @param $feature_name string
	 * @return ?string
	 */
	public function run(Parameters $parameters, array $form, array $files, string $feature_name)
		: ?string;

}
