<?php
namespace SAF\Framework\Controller;

use SAF\Framework\Controller;

/**
 * The default feature controller interface
 *
 * Implement your default controllers for a given feature using this.
 */
interface Default_Feature_Controller extends Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * Run method for a feature controller working for any class
	 *
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array
	 * @param $class_name string
	 * @return mixed
	 */
	public function run(Parameters $parameters, $form, $files, $class_name);

}
