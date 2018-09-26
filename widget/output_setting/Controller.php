<?php
namespace ITRocks\Framework\Widget\Output_Setting;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Default_Feature_Controller;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Getter;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\View;
use ITRocks\Framework\Widget\Output;

/**
 * Default output setting feature controller
 */
class Controller implements Default_Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @param $class_name string
	 * @return mixed
	 */
	public function run(Parameters $parameters, array $form, array $files, $class_name)
	{
		$parameters = $parameters->getObjects();
		$feature = isset($parameters[Feature::FEATURE])
			? $parameters[Feature::FEATURE]
			: Feature::F_OUTPUT;
		$controller_class = Getter::get($class_name, $feature, 'Controller', 'php')[0];
		/** @var $output_controller Output\Controller */
		$output_controller = Builder::create($controller_class);
		$output_settings   = Set::current($class_name, $feature);
		$output_controller->applyParametersToOutputSettings($output_settings, $parameters, $form);
		$parameters = array_merge([$class_name => Builder::create($class_name)], $parameters);
		return View::run($parameters, $form, $files, $class_name, 'outputSetting');
	}

}
