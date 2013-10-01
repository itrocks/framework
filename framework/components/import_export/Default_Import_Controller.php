<?php
namespace SAF\Framework;

/**
 * Default import controller
 */
class Default_Import_Controller implements Default_Feature_Controller
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
		$sub_feature = $parameters->shiftUnnamed();
		if (!$sub_feature) {
			$sub_feature = "form";
		}
		$get = $parameters->toGet();
		$feature = "import" . ucfirst($sub_feature);
		return Main_Controller::getInstance()->runController(
			"/" . $class_name . "/" . $feature, $get, $form, $files
		);
	}

}
