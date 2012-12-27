<?php
namespace SAF\Framework;

interface Default_Feature_Controller extends Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * Run method for a feature controller working for any class
	 *
	 * @param Controller_Parameters $parameters
	 * @param array  $form
	 * @param array  $files
	 * @param string $class_name
	 */
	public function run(Controller_Parameters $parameters, $form, $files, $class_name);

}
