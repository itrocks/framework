<?php
namespace ITRocks\Framework\Feature\Edit;

use ITRocks\Framework\Component\Button;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Controller\Tag;
use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Feature\Output;
use ITRocks\Framework\Feature\Output_Setting;
use ITRocks\Framework\Setting;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\View;

/**
 * The default edit controller, when no edit controller is set for the class
 *
 * @feature @built-in Edit your business objects and documents into standard forms
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
		[$close_link, $follows] = $this->prepareThen(
			$object,
			$parameters,
			View::link(is_object($object) ? $object : Names::classToSet(is_object($object)))
		);
		if ($settings && $settings->actions) {
			$buttons = parent::getGeneralButtons($object, $parameters, $settings);
			unset($buttons[Feature::F_EDIT]);
			unset($buttons[Feature::F_PRINT]);
		}
		else {
			$fill_combo = isset($parameters['fill_combo'])
				? ['fill_combo' => $parameters['fill_combo']]
				: [];
			$buttons = [
				Feature::F_CLOSE => new Button(
					'Close',
					$close_link,
					Feature::F_CLOSE,
					Target::MAIN
				),
				Feature::F_SAVE => new Button(
					'Save',
					View::link($object, Feature::F_SAVE, null, array_merge($fill_combo, $follows)),
					Feature::F_SAVE,
					[Target::RESPONSES, Tag::SUBMIT]
				)
			];
		}
		if (Dao::getObjectIdentifier($object) && !isset($buttons[Feature::F_DELETE])) {
			$buttons[Feature::F_DELETE] = new Button(
				'Delete',
				View::link($object, Feature::F_DELETE, null, $follows),
				Feature::F_DELETE,
				Target::RESPONSES
			);
		}
		return $buttons;
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
