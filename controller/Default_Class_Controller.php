<?php
namespace ITRocks\Framework\Controller;

use ITRocks\Framework\Controller;

/**
 * A default class controller, called for a given feature
 */
interface Default_Class_Controller extends Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * Default run method for the class controller, when no runFeatureName() method was found in it.
	 *
	 * Class controllers must implement this method if you want the controller to work.
	 *
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @param $class_name string
	 * @return ?string
	 */
	public function run(Parameters $parameters, array $form, array $files, string $class_name)
		: ?string;

}
