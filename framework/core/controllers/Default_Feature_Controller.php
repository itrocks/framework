<?php
namespace SAF\Framework;

interface Default_Feature_Controller extends Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * Run method for a feature controller working for any class
	 *
	 * @param $parameters Controller_Parameters
	 * @param $form array
	 * @param $files array
	 * @param $class_name string
	 * @return mixed
	 */
	public function run(Controller_Parameters $parameters, $form, $files, $class_name);

}
