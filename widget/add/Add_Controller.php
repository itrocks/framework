<?php
namespace SAF\Framework\Widget\Add;

use SAF\Framework\Controller\Feature;
use SAF\Framework\Controller\Parameters;
use SAF\Framework\Controller\Target;
use SAF\Framework\Reflection\Reflection_Class;
use SAF\Framework\Setting\Custom_Settings;
use SAF\Framework\Tools\Color;
use SAF\Framework\Tools\Names;
use SAF\Framework\View;
use SAF\Framework\Widget\Button;
use SAF\Framework\Widget\Edit\Edit_Controller;
use SAF\Framework\Widget\Output_Setting\Output_Settings;

/**
 * The default new controller is the same as an edit controller, that accepts no object
 */
class Add_Controller extends Edit_Controller
{

	//----------------------------------------------------------------------------- getGeneralButtons
	/**
	 * @param $object     object|string object or class name
	 * @param $parameters array parameters
	 * @param $settings   Custom_Settings|Output_Settings
	 * @return Button[]
	 */
	public function getGeneralButtons($object, $parameters, Custom_Settings $settings = null)
	{
		$buttons = parent::getGeneralButtons($object, $parameters, $settings);

		$close_link = View::link(Names::classToSet(get_class($object)));
		list($close_link) = $this->prepareThen($object, $parameters, $close_link);

		return array_merge($buttons, [
			Feature::F_CLOSE => new Button(
				'Close', $close_link, Feature::F_CLOSE, [new Color('close'), Target::MAIN]
			),
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
		$object = $parameters->getMainObject($class_name);
		foreach ((new Reflection_Class($class_name))->accessProperties() as $property) {
			$property->setValue($object, $property->getDefaultValue());
		}
		$objects = $parameters->getObjects();
		if (count($objects) > 1) {
			foreach (array_slice($objects, 1) as $property_name => $value) {
				$object->$property_name = $value;
			}
		}
		return parent::getViewParameters($parameters, $form, $class_name);
	}

}
