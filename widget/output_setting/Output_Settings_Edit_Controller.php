<?php
namespace ITRocks\Framework\Widget\Output_Setting;

use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\View;

/**
 * Output setting widget output edit controller
 */
class Output_Settings_Edit_Controller implements Feature_Controller
{

	//----------------------------------------------------------- applyCustomSettingsToOutputSettings
	/**
	 * @param $class_name string The name of the class
	 * @param $feature    string The feature
	 * @return Output_Settings
	 */
	private function applyCustomSettingsToOutputSettings($class_name, $feature)
	{
		$output_settings = Output_Settings::current($class_name, $feature);
		$output_settings->cleanup();
		return $output_settings;
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * This will be called for this controller, always.
	 *
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @return mixed
	 */
	public function run(Parameters $parameters, array $form, array $files)
	{
		list($class_name, $feature) = $parameters->getRawParameters();
		$output_settings = $this->applyCustomSettingsToOutputSettings($class_name, $feature);
		$parameters->unshift($output_settings);
		$parameters = $parameters->getObjects();
		return View::run($parameters, $form, $files, Output_Settings::class, Feature::F_EDIT);
	}

}
