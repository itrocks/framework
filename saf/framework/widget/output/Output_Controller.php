<?php
namespace SAF\Framework\Widget\Output;

use SAF\Framework\Controller;
use SAF\Framework\Controller\Default_Feature_Controller;
use SAF\Framework\Controller\Feature;
use SAF\Framework\Controller\Parameter;
use SAF\Framework\Controller\Parameters;
use SAF\Framework\Controller\Target;
use SAF\Framework\Printer\Model;
use SAF\Framework\Reflection\Annotation\Property\User_Annotation;
use SAF\Framework\Reflection\Reflection_Class;
use SAF\Framework\Setting\Buttons;
use SAF\Framework\Setting\Custom_Settings_Controller;
use SAF\Framework\Tools\Color;
use SAF\Framework\Tools\Names;
use SAF\Framework\Tools\Namespaces;
use SAF\Framework\View;
use SAF\Framework\Widget\Button;
use SAF\Framework\Widget\Duplicate\Duplicate;
use SAF\Framework\Widget\Output_Setting\Output_Settings;
use SAF\Framework\Widget\Tab;
use SAF\Framework\Widget\Tab\Tabs_Builder_Object;

/**
 * All output controllers should extend from this at it offers standard output elements methods and structure
 */
class Output_Controller implements Default_Feature_Controller
{

	//--------------------------------------------------------------------------- applyOutputSettings
	/**
	 * @param $output_settings Output_Settings
	 */
	private function applyOutputSettings(Output_Settings $output_settings)
	{
		$class = new Reflection_Class($output_settings->class_name);
		if ($output_settings->properties_read_only) {
			$user_annotation = new User_Annotation(User_Annotation::READONLY);
			foreach ($output_settings->properties_read_only as $property_path => $read_only) {
				$property = $class->getProperty($property_path);
				if (!$property->getAnnotation(User_Annotation::ANNOTATION)->value) {
					$class->getProperty($property_path)->setAnnotation(
						User_Annotation::ANNOTATION, $user_annotation
					);
				}
			}
		}
	}

	//--------------------------------------------------------------- applyParametersToOutputSettings
	/**
	 * Apply parameters to output settings
	 *
	 * @param $output_settings Output_Settings
	 * @param $parameters      array
	 * @param $form            array
	 * @return Output_Settings set if parameters did change
	 */
	public function applyParametersToOutputSettings(
		Output_Settings &$output_settings, $parameters, $form = null
	) {
		if (isset($form)) {
			$parameters = array_merge($parameters, $form);
		}
		$did_change = false;
		if (isset($parameters['add_property'])) {
			$output_settings->addProperty(
				$parameters['add_property'],
				$parameters['tab'],
				isset($parameters['before']) ? 'before' : 'after',
				isset($parameters['before'])
					? $parameters['before']
					: (isset($parameters['after']) ? $parameters['after'] : '')
			);
			$did_change = true;
		}
		if (
			isset($parameters['conditions'])
			&& ($output_settings->conditions != $parameters['conditions'])
		) {
			$output_settings->conditions = $parameters['conditions'];
			$did_change = true;
		}
		if (isset($parameters['property_path'])) {
			if (isset($parameters['property_read_only'])) {
				$output_settings->propertyReadOnly(
					$parameters['property_path'], $parameters['property_read_only']
				);
			}
			if (isset($parameters['property_title'])) {
				$output_settings->propertyTitle(
					$parameters['property_path'], $parameters['property_title']
				);
			}
			$did_change = true;
		}
		if (isset($parameters['remove_property'])) {
			$output_settings->removeProperty($parameters['remove_property']);
			$did_change = true;
		}
		if (isset($parameters['title']) && ($parameters['title'] != $output_settings->title)) {
			$output_settings->title = $parameters['title'];
			$did_change = true;
		}
		if (
			Custom_Settings_Controller::applyParametersToCustomSettings($output_settings, $parameters)
		) {
			$did_change = true;
		}
		if (!$output_settings->name && strlen($output_settings->title)) {
			$output_settings->name = $output_settings->title;
			$did_change = true;
		}
		if ($did_change) {
			$output_settings->save();
		}
		return $did_change ? $output_settings : null;
	}

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
			[new Color(Feature::F_CLOSE), Target::MAIN]
		);
		$buttons[Feature::F_EDIT] = new Button(
			'Edit',
			View::link($object, Feature::F_EDIT, null, $follows),
			Feature::F_EDIT,
			[new Color(Color::GREEN), Target::MAIN]
		);
		if ($object instanceof Duplicate) {
			$buttons[Feature::F_EDIT]->sub_buttons[Feature::F_DUPLICATE] = new Button(
				'Duplicate',
				View::link($object, Feature::F_DUPLICATE, null, $follows),
				Feature::F_DUPLICATE,
				[new Color(Color::MAGENTA), Target::MAIN]
			);
		}
		$buttons[Feature::F_PRINT] = new Button(
			'Print',
			View::link($object, Feature::F_PRINT),
			Feature::F_PRINT,
			[new Color(Color::BLUE), Target::MAIN, Button::SUB_BUTTONS => [
				new Button(
					'Models',
					View::link(
						Names::classToSet(Model::class),
						Feature::F_LIST,
						Namespaces::shortClassName(get_class($object))
					),
					Feature::F_LIST,
					Target::MAIN
				)
			]]
		);
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

	//---------------------------------------------------------------------------------------- getTab
	/**
	 * Get output tab for a given object
	 *
	 * @param $object          object
	 * @param $output_settings Output_Settings
	 * @return Tab[]
	 */
	protected function getTab($object, $output_settings)
	{
		if (isset($output_settings->tab)) {
			$tab = $output_settings->tab->propertiesToValues($object, $output_settings->properties_title);
		}
		else {
			$tab = new Tab('main');
			$tab->includes = Tabs_Builder_Object::buildObject($object, $output_settings->properties_path);
		}
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
		$output_settings = Output_Settings::current($class_name);
		$output_settings->cleanup();
		$this->applyParametersToOutputSettings($output_settings, $parameters, $form);
		$this->applyOutputSettings($output_settings);
		$parameters['customized_lists']           = $output_settings->getCustomSettings();
		$parameters['default_title']              = ucfirst(Names::classToDisplay($class_name));
		$parameters[Parameter::PROPERTIES_FILTER] = $output_settings->properties_path;
		$parameters[Parameter::PROPERTIES_TITLE]  = $output_settings->properties_title;
		$parameters['settings']                   = $output_settings;
		$parameters['tabs']                       = $this->getTab($object, $output_settings);
		$parameters['title']                      = $output_settings->title();
		// buttons
		$feature = isset($parameters[Feature::FEATURE])
			? $parameters[Feature::FEATURE]
			: Feature::F_OUTPUT;
		$parameters['custom_buttons'] = (new Buttons())->getButtons(
			'custom ' . $feature, $object, $feature /* , Target::MESSAGES TODO back but do not display output */
		);
		$parameters['general_buttons'] = $this->getGeneralButtons($object, $parameters);
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
			$follows    = [Controller::THEN => $parameters[Controller::THEN]];
		}
		else {
			$close_link = $default_close_link ?: View::link(Names::classToSet(get_class($object)));
			$follows    = [];
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
