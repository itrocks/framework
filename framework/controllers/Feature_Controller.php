<?php
namespace Framework;

interface Feature_Controller extends Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param Controller_Parameters $parameters
	 * @param array  $form
	 * @param array  $files
	 */
	public function run($parameters, $form, $files);

}
