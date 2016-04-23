<?php
namespace SAF\Framework\Widget\Output_Setting;

use SAF\Framework\Builder;
use SAF\Framework\Controller\Default_Feature_Controller;
use SAF\Framework\Controller\Feature;
use SAF\Framework\Controller\Getter;
use SAF\Framework\Controller\Parameters;
use SAF\Framework\View;
use SAF\Framework\Widget\Output\Output_Controller;

/**
 * Default output setting feature controller
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
		$feature = isset($parameters[Feature::FEATURE])
			? $parameters[Feature::FEATURE]
			: Feature::F_OUTPUT;
		$controller_class = Getter::get($class_name, $feature, 'Controller', 'php')[0];
		/** @var $output_controller Output_Controller */
		$output_controller = Builder::create($controller_class);
		$output_settings = Output_Settings::current($class_name, $feature);
		$output_controller->applyParametersToOutputSettings($output_settings, $parameters, $form);
		$parameters = array_merge([$class_name => Builder::create($class_name)], $parameters);
		return View::run($parameters, $form, $files, $class_name, 'outputSetting');
	}

}
