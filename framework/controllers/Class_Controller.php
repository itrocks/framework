<?php
namespace Framework;

interface Class_Controller extends Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param Controller_Parameters $parameters
	 * @param array  $form
	 * @param array  $files
	 * @param string $class_name
	 */
	public function run($parameters, $form, $files, $class_name);

}
