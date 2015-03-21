<?php
namespace SAF\Framework\Widget\Edit;

use SAF\Framework\Controller\Feature;
use SAF\Framework\Controller\Parameters;
use SAF\Framework\Tools\Color;
use SAF\Framework\View;
use SAF\Framework\Widget\Button;
use SAF\Framework\widget\output\Output_Controller;

/**
 * The default edit controller, when no edit controller is set for the class
 */
class Edit_Controller extends Output_Controller
{

	//----------------------------------------------------------------------------- getGeneralButtons
	/**
	 * @param $object     object|string object or class name
	 * @param $parameters string[] parameters
	 * @return Button[]
	 */
	protected function getGeneralButtons($object, $parameters)
	{
		$buttons = parent::getGeneralButtons($object, $parameters);
		unset($buttons['edit']);
		$fill_combo = isset($parameters['fill_combo'])
			? ['fill_combo' => $parameters['fill_combo']]
			: [];
		return array_merge($buttons, [
			Feature::F_CLOSE => new Button(
				'Close',
				View::link($object),
				Feature::F_CLOSE,
				[new Color('close'), '#main']
			),
			Feature::F_WRITE => new Button(
				'Write',
				View::link($object, Feature::F_WRITE, null, $fill_combo),
				Feature::F_WRITE,
				[new Color('green'), '#messages', '.submit']
			)
		]);
	}

	//----------------------------------------------------------------------------- getViewParameters
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $class_name string
	 * @return mixed[]
	 */
	protected function getViewParameters(Parameters $parameters, $form, $class_name)
	{
		$parameters = parent::getViewParameters($parameters, $form, $class_name);
		$parameters['editing']            = true;
		$parameters[Feature::FEATURE]     = Feature::F_EDIT;
		$parameters['template_namespace'] = __NAMESPACE__;
		return $parameters;
	}

}
