<?php
namespace SAF\Framework\Widget\Output;

use SAF\Framework\Controller;
use SAF\Framework\Controller\Default_Feature_Controller;
use SAF\Framework\Controller\Feature;
use SAF\Framework\Controller\Parameters;
use SAF\Framework\Print_Model;
use SAF\Framework\Tools\Color;
use SAF\Framework\Tools\Names;
use SAF\Framework\View;
use SAF\Framework\Widget\Button;
use SAF\Framework\Widget\Duplicate\Duplicate;
use SAF\Framework\Widget\Tab;
use SAF\Framework\Widget\Tab\Tabs_Builder_Object;

/**
 * All output controllers should extend from this at it offers standard output elements methods and structure
 */
class Output_Controller implements Default_Feature_Controller
{

	//----------------------------------------------------------------------------- getGeneralButtons
	/**
	 * @param $object     object|string object or class name
	 * @param $parameters string[] parameters
	 * @return Button[]
	 */
	protected function getGeneralButtons($object, $parameters)
	{
		list($close_link, $follows) = $this->prepareThen($object, $parameters);
		$buttons[Feature::F_CLOSE] = new Button(
			'Close',
			$close_link,
			Feature::F_CLOSE,
			[new Color('close'), '#main']
		);
		$buttons[Feature::F_EDIT] = new Button(
			'Edit',
			View::link($object, Feature::F_EDIT, null, $follows),
			Feature::F_EDIT,
			[new Color(Color::GREEN), '#main']
		);
		if ($object instanceof Duplicate) {
			$buttons[Feature::F_EDIT]->sub_buttons['duplicate'] = new Button(
				'Duplicate',
				View::link($object, Feature::F_DUPLICATE, null, $follows),
				Feature::F_DUPLICATE,
				['#main']
			);
		}
		/*,
		new Button('Print', View::link($object, 'print'), 'print',
			[new Color(Color::BLUE), '#main', 'sub_buttons' => [
				new Button(
					'Models',
					View::link(
						Names::classToSet(Print_Model::class), Feature::F_LIST,
						Namespaces::shortClassName(get_class($object))
					),
					'models',
					'#main'
				)
			]]
		)
		*/
		return $buttons;
	}

	//----------------------------------------------------------------------------- getPropertiesList
	/**
	 * @param $class_name string
	 * @return string[] property names list
	 */
	protected function getPropertiesList(
		/** @noinspection PhpUnusedParameterInspection */
		$class_name
	) {
		return null;
	}

	//--------------------------------------------------------------------------------------- getTabs
	/**
	 * Get output tabs list for a given object
	 *
	 * @param $object object
	 * @param $properties string[] Can be null
	 * @return Tab[]
	 */
	protected function getTabs($object, $properties)
	{
		$tab = new Tab('main');
		$tab->includes = Tabs_Builder_Object::buildObject($object, $properties);
		return $tab;
	}

	//----------------------------------------------------------------------------- getViewParameters
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $class_name string
	 * @return mixed[]
	 */
	protected function getViewParameters(
		/** @noinspection PhpUnusedParameterInspection */
		Parameters $parameters, $form, $class_name
	) {
		$object = $parameters->getMainObject($class_name);
		$parameters = $parameters->getObjects();
		$parameters['general_buttons']   = $this->getGeneralButtons($object, $parameters);
		$parameters['properties_filter'] = $this->getPropertiesList($class_name);
		$parameters['tabs']              = $this->getTabs($object, $parameters['properties_filter']);
		return $parameters;
	}

	//----------------------------------------------------------------------------------- prepareThen
	/**
	 * Prepare close link and follows links for buttons
	 *
	 * @example Call this from getGeneralButtons() :
	 * list($close_link, $follows) = $this->prepareThen($object, $parameters);
	 * Then use $close_link and $follows as needed
	 * @param $object             object
	 * @param $parameters         array
	 * @param $default_close_link string
	 * @return array first element is the close link, second element is an array of a link parameter
	 */
	protected function prepareThen($object, $parameters, $default_close_link = null)
	{
		if (isset($parameters[Controller::THEN])) {
			$close_link = $parameters[Controller::THEN];
			$follows = [Controller::THEN => $parameters[Controller::THEN]];
		}
		else {
			$close_link = $default_close_link ?: View::link(Names::classToSet(get_class($object)));
			$follows = [];
		}
		return [$close_link, $follows];
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * Default run method for default output view controller
	 *
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array
	 * @param $class_name string
	 * @return mixed
	 */
	public function run(Parameters $parameters, $form, $files, $class_name)
	{
		$parameters = $this->getViewParameters($parameters, $form, $class_name);
		return View::run($parameters, $form, $files, $class_name, Feature::F_OUTPUT);
	}

}
