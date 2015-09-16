<?php
namespace SAF\Framework\Widget\Button;

use SAF\Framework\Builder;
use SAF\Framework\Controller\Feature_Controller;
use SAF\Framework\Controller\Parameters;
use SAF\Framework\Controller\Uri;
use SAF\Framework\Mapper\Object_Builder_Array;
use SAF\Framework\View;
use SAF\Framework\Widget\Button;
use SAF\Framework\Widget\Output_Setting\Output_Setting_Controller;
use SAF\Framework\Widget\Write;

/**
 * Button write controller
 */
class Write_Controller implements Feature_Controller
{

	//------------------------------------------------------------------- callOutputSettingController
	/**
	 * @param $button Button
	 * @param $form   string[]
	 */
	public function callOutputSettingController(Button $button, $form)
	{
		/** @var $parameters Parameters */
		$parameters = Builder::create(Parameters::class, [
			new Uri($form['custom_class_name'] . SL . $form['custom_feature'])
		]);
		$parameters->set('add_action', $button);
		if (isset($form['custom_after_button'])) {
			$parameters->set('after', $form['custom_after_button']);
		}
		elseif (isset($form['custom_before_button'])) {
			$parameters->set('before', $form['custom_before_button']);
		}
		/** @var $output_setting_controller Output_Setting_Controller */
		$output_setting_controller = Builder::create(Output_Setting_Controller::class);
		$output_setting_controller->run($parameters, [], [], $form['custom_class_name']);
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * Adds an action button after/before another action button
	 *
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array
	 * @return mixed
	 */
	public function run(Parameters $parameters, $form, $files)
	{
		/** @var $button Button */
		$button = $parameters->getMainObject(Button::class);
		/** @var $builder Object_Builder_Array */
		$builder = Builder::create(Object_Builder_Array::class);
		$builder->ignore_unknown_properties = true;
		$button = $builder->build($form, $button);
		$this->callOutputSettingController($button, $form);
		return View::run($parameters->getObjects(), $form, $files, get_class($button), 'added');
	}

}
