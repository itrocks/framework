<?php
namespace SAF\Framework;

/**
 * Default importForm feature controller
 */
class Default_Import_Form_Controller implements Default_Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Controller_Parameters
	 * @param $form       array
	 * @param $files      array
	 * @param $class_name string
	 * @return mixed
	 */
	public function run(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$parameters = $parameters->getObjects();
		array_unshift($parameters, new Import($class_name));
		return View::run($parameters, $form, $files, $class_name, "importForm");
	}

}
