<?php
namespace ITRocks\Framework\Feature\Output_Setting;

use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\View;

/**
 * Output setting widget output edit controller
 */
class Edit_Controller implements Feature_Controller
{

	//----------------------------------------------------------- applyCustomSettingsToOutputSettings
	/**
	 * @param $class_name string The name of the class
	 * @param $feature    string The feature
	 * @return Set
	 */
	private function applyCustomSettingsToOutputSettings(string $class_name, string $feature) : Set
	{
		$output_settings = Set::current($class_name, $feature);
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
	 * @return ?string
	 */
	public function run(Parameters $parameters, array $form, array $files) : ?string
	{
		[$class_name, $feature] = $parameters->getRawParameters();
		$output_settings        = $this->applyCustomSettingsToOutputSettings($class_name, $feature);
		$parameters->unshift($output_settings);
		$parameters = $parameters->getObjects();
		return View::run($parameters, $form, $files, Set::class, Feature::F_EDIT);
	}

}
