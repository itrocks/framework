<?php
namespace SAF\Framework\Widget\Output_Setting;

use SAF\Framework\Controller\Feature;
use SAF\Framework\Controller\Feature_Controller;
use SAF\Framework\Controller\Parameters;
use SAF\Framework\View;

/**
 * Output setting widget output edit controller
 */
class Output_Settings_Edit_Controller implements Feature_Controller
{

	//----------------------------------------------------------- applyCustomSettingsToOutputSettings
	/**
	 * @param $class_name string The name of the class
	 * @return Output_Settings
	 */
	private function applyCustomSettingsToOutputSettings($class_name)
	{
		$output_settings = Output_Settings::current($class_name);
		$output_settings->cleanup();
		return $output_settings;
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * This will be called for this controller, always.
	 *
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array
	 * @return mixed
	 */
	public function run(Parameters $parameters, $form, $files)
	{
		$class_name = $parameters->getRawParameter(0);
		$output_settings = $this->applyCustomSettingsToOutputSettings($class_name);
		$parameters->unshift($output_settings);
		$parameters = $parameters->getObjects();
		return View::run($parameters, $form, $files, Output_Settings::class, Feature::F_EDIT);
	}

}
