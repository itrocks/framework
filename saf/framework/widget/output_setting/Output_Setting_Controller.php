<?php
namespace SAF\Framework\Widget\Output_Setting;

use SAF\Framework\Controller\Default_Feature_Controller;
use SAF\Framework\Controller\Parameters;
use SAF\Framework\View;
use SAF\Framework\Widget\Output\Output_Controller;

/**
 * Default data list setting feature controller
 */
class Output_Setting_Controller implements Default_Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array
	 * @param $class_name string
	 * @return mixed
	 */
	public function run(Parameters $parameters, $form, $files, $class_name)
	{
		$parameters = $parameters->getObjects();
		$output_controller = new Output_Controller();
		$output_settings = Output_Settings::current($class_name);
		$output_controller->applyParametersToOutputSettings($output_settings, $parameters, $form);
		return View::run($parameters, $form, $files, $class_name, 'outputSetting');
	}

}
