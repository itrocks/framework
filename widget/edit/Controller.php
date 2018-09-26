<?php
namespace ITRocks\Framework\Widget\Edit;

use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Controller\Tag;
use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\Setting;
use ITRocks\Framework\Tools\Color;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\View;
use ITRocks\Framework\Widget\Button;
use ITRocks\Framework\Widget\Output;
use ITRocks\Framework\Widget\Output_Setting;

/**
 * The default edit controller, when no edit controller is set for the class
 */
class Controller extends Output\Controller
{

	//------------------------------------------------------------------------------- HIDE_EMPTY_TEST
	/**
	 * Parameter for Reflection_Property::isVisible (for tabs)
	 */
	const HIDE_EMPTY_TEST = false;

	//----------------------------------------------------------------------------- getGeneralButtons
	/**
	 * @param $object     object|string object or class name
	 * @param $parameters array parameters
	 * @param $settings   Setting\Custom\Set|Output_Setting\Set
	 * @return Button[]
	 */
	public function getGeneralButtons($object, array $parameters, Setting\Custom\Set $settings = null)
	{
		list($close_link, $follows) = $this->prepareThen(
			$object,
			$parameters,
			View::link(Names::classToSet(is_object($object) ? get_class($object) : $object))
		);
		$buttons = parent::getGeneralButtons($object, $parameters, $settings);
		unset($buttons[Feature::F_EDIT]);
		unset($buttons[Feature::F_PRINT]);
		$fill_combo = isset($parameters['fill_combo'])
			? ['fill_combo' => $parameters['fill_combo']]
			: [];
		return ($settings && $settings->actions)
			? $buttons
			: array_merge([
				Feature::F_CLOSE => new Button(
					'Close',
					$close_link,
					Feature::F_CLOSE,
					[new Color(Feature::F_CLOSE), Target::MAIN]
				),
				Feature::F_WRITE => new Button(
					'Write',
					View::link($object, Feature::F_WRITE, null, array_merge($fill_combo, $follows)),
					Feature::F_WRITE,
					[new Color(Color::GREEN), Target::MESSAGES, Tag::SUBMIT]
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
	protected function getViewParameters(Parameters $parameters, array $form, $class_name)
	{
		$parameters->set('editing', true);
		$parameters->set(Feature::FEATURE, Feature::F_EDIT);
		$parameters = parent::getViewParameters($parameters, $form, $class_name);
		$parameters['template_namespace'] = __NAMESPACE__;
		return $parameters;
	}

}
