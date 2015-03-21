<?php
namespace SAF\Framework\Widget\Add;

use SAF\Framework\Controller\Feature;
use SAF\Framework\Controller\Parameters;
use SAF\Framework\Tools\Color;
use SAF\Framework\Tools\Names;
use SAF\Framework\View;
use SAF\Framework\Widget\Button;
use SAF\Framework\Widget\Edit\Edit_Controller;

/**
 * The default new controller is the same as an edit controller, that accepts no object
 */
class Add_Controller extends Edit_Controller
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
		return array_merge($buttons, [
			Feature::F_CLOSE => new Button('Close', View::link(Names::classToSet(get_class($object))),
				Feature::F_CLOSE, [new Color('close'), '#main']
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
		$objects = $parameters->getObjects();
		if (count($objects) > 1) {
			foreach (array_slice($objects, 1) as $property_name => $value) {
				$object->$property_name = $value;
			}
		}
		return parent::getViewParameters($parameters, $form, $class_name);
	}

}
