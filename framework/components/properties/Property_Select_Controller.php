<?php
namespace SAF\Framework;

class Property_Select_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * Property select controller, starting from a given root class
	 *
	 * @param $parameters Controller_Parameters
	 * - first : the reference class name (ie a business object)
	 * @param $form array  not used
	 * @param $files array not used
	 */
	public function run(Controller_Parameters $parameters, $form, $files)
	{
		$objects = $parameters->getObjects();
		$objects["class_name"] = array_shift($objects);
		View::run($objects, $form, $files, __NAMESPACE__ . "\\Property", "select");
	}

}
