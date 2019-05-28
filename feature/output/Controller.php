<?php
namespace ITRocks\Framework\Feature\Output;

use ITRocks\Framework\Component\Button;
use ITRocks\Framework\Component\Button\Code;
use ITRocks\Framework\Component\Button\Has_General_Buttons;
use ITRocks\Framework\Component\Tab;
use ITRocks\Framework\Component\Tab\Tabs_Builder_Object;
use ITRocks\Framework\Controller\Default_Feature_Controller;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Parameter;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Feature\Duplicate;
use ITRocks\Framework\Feature\Output_Setting;
use ITRocks\Framework\Layout\Print_Model\Buttons_Generator;
use ITRocks\Framework\Reflection\Annotation\Property\Group_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\User_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Setting;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\View;

/**
 * All output controllers should extend from this at it offers standard output elements methods and structure
 */
class Controller implements Default_Feature_Controller, Has_General_Buttons
{

	//------------------------------------------------------------------------------- HIDE_EMPTY_TEST
	/**
	 * Parameter for Reflection_Property::isVisible (for tabs)
	 */
	const HIDE_EMPTY_TEST = true;

	//--------------------------------------------------------------------------- applyOutputSettings
	/**
	 * Apply output settings rules to output settings properties
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $output_settings Output_Setting\Set
	 */
	protected function applyOutputSettings(Output_Setting\Set $output_settings)
	{
		if ($output_settings->properties) {
			foreach ($output_settings->properties as $property_path => $property) {
				/** @noinspection PhpUnhandledExceptionInspection class and property must be valid */
				$reflection_property = new Reflection_Property(
					$output_settings->getClassName(), $property_path
				);
				$user_annotation = $reflection_property->getListAnnotation(User_Annotation::ANNOTATION);
				$property->hide_empty
					? $user_annotation->add(User_Annotation::HIDE_EMPTY)
					: $user_annotation->remove(User_Annotation::HIDE_EMPTY);
				$property->read_only
					? $user_annotation->add(User_Annotation::READONLY)
					: $user_annotation->remove(User_Annotation::READONLY);
				$property->tooltip
					? $user_annotation->add(User_Annotation::TOOLTIP)
					: $user_annotation->remove(User_Annotation::TOOLTIP);
				if (!is_null($property->tab_name)) {
					$group_annotation = $reflection_property->getAnnotation(Group_Annotation::ANNOTATION);
					$group_annotation->value = $property->tab_name;
				}
			}
		}
	}

	//--------------------------------------------------------------- applyParametersToOutputSettings
	/**
	 * Apply parameters to output settings
	 *
	 * @param $output_settings Output_Setting\Set
	 * @param $parameters      array
	 * @param $form            array
	 * @return Output_Setting\Set set if parameters did change
	 */
	public function applyParametersToOutputSettings(
		Output_Setting\Set &$output_settings, array $parameters, array $form = null
	) {
		if (isset($form)) {
			$parameters = array_merge($parameters, $form);
		}
		$did_change = isset($parameters[Parameter::DID_CHANGE])
			? $parameters[Parameter::DID_CHANGE]
			: false;
		if (isset($parameters['add_action'])) {
			if (!$output_settings->actions) {
				$output_settings->actions = $this->getGeneralButtons(
					$output_settings->getClassName(), $parameters
				);
			}
			$output_settings->addAction(
				$parameters['add_action'],
				isset($parameters['before']) ? 'before' : 'after',
				isset($parameters['before']) ? $parameters['before'] : $parameters['after']
			);
			$did_change = true;
		}
		if (isset($parameters['add_property'])) {
			$output_settings->addProperty(
				$parameters['add_property'],
				isset($parameters['tab']) ? $parameters['tab'] : '',
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
			if (isset($parameters['property_hide_empty'])) {
				$output_settings->propertyHideEmpty(
					$parameters['property_path'], $parameters['property_hide_empty']
				);
			}
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
			if (isset($parameters['property_tooltip'])) {
				$output_settings->propertyTooltip(
					$parameters['property_path'], $parameters['property_tooltip']
				);
			}
			$did_change = true;
		}
		if (isset($parameters['remove_action'])) {
			if ($output_settings->removeAction($parameters['remove_action'])) {
				$did_change = true;
			}
		}
		if (isset($parameters['remove_property'])) {
			$output_settings->removeProperty($parameters['remove_property']);
			$did_change = true;
		}
		if (isset($parameters['title']) && ($parameters['title'] !== $output_settings->title)) {
			$output_settings->name  = $parameters['title'];
			$output_settings->title = $parameters['title'];
			$did_change = true;
		}
		if (Setting\Custom\Controller::applyParametersToCustomSettings($output_settings, $parameters)) {
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
	 * @param $parameters array parameters
	 * @param $settings   Setting\Custom\Set|Output_Setting\Set
	 * @return Button[]
	 */
	public function getGeneralButtons($object, array $parameters, Setting\Custom\Set $settings = null)
	{
		list($close_link, $follows) = $this->prepareThen($object, $parameters);
		$buttons[Feature::F_CLOSE] = new Button(
			'Close', $close_link, Feature::F_CLOSE
		);
		$buttons[Feature::F_EDIT] = new Button(
			'Edit', View::link($object, Feature::F_EDIT, null, $follows), Feature::F_EDIT
		);
		if (
			/** @noinspection PhpUnhandledExceptionInspection object */
			($object instanceof Duplicate) || (new Reflection_Class($object))->getAnnotations('duplicate')
		) {
			$buttons[Feature::F_EDIT]->sub_buttons[Feature::F_DUPLICATE] = new Button(
				'Duplicate', View::link($object, Feature::F_DUPLICATE, null, $follows), Feature::F_DUPLICATE
			);
		}

		$layout_model_buttons      = (new Buttons_Generator($object))->getButtons();
		$buttons[Feature::F_PRINT] = new Button(
			'Print',
			View::link($object, Feature::F_PRINT),
			Feature::F_PRINT,
			[Button::SUB_BUTTONS => $layout_model_buttons]
		);
		$this->selectPrintButton($buttons[Feature::F_PRINT], $layout_model_buttons);

		if (Dao::getObjectIdentifier($object)) {
			$buttons[Feature::F_DELETE] = new Button(
				'Delete',
				View::link($object, Feature::F_DELETE, null, $follows),
				Feature::F_DELETE,
				[Target::MESSAGES]
			);
		}

		if ($settings && $settings->actions) {
			// default buttons on settings are false : get the default buttons from getGeneralButtons
			// whet they are set into output settings
			foreach ($settings->actions as $button_key => $button) {
				if (isset($buttons[$button_key])) {
					$settings->actions[$button_key] = $buttons[$button_key];
				}
				else {
					$settings->actions[$button_key]->setObjectContext($object);
				}
			}
			$buttons = $settings->actions;
		}

		// remove buttons whose conditions do not apply
		foreach ($buttons as $key => $button) {
			if (!$button->conditionsApplyTo($object)) {
				unset($buttons[$key]);
			}
		}

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
	 * @param $output_settings Output_Setting\Set
	 * @return Tab
	 */
	protected function getTab($object, $output_settings)
	{
		if (isset($output_settings->tab)) {
			$properties_display = $output_settings->propertiesParameter('display');
			$tab = $output_settings->tab->propertiesToValues($object, $properties_display);
		}
		else {
			$tab = new Tab('main');
			$tab->includes = (new Tabs_Builder_Object)->build(
				$object, array_keys($output_settings->properties)
			);
		}
		$tab->filterVisibleProperties(static::HIDE_EMPTY_TEST);
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
		Parameters $parameters, array $form, $class_name
	) {
		$object     = $parameters->getMainObject($class_name);
		$parameters = $parameters->getObjects();
		$feature    = isset($parameters[Feature::FEATURE])
			? $parameters[Feature::FEATURE]
			: Feature::F_OUTPUT;

		// apply parameters / form to current output settings
		$output_settings = $this->outputSettings($class_name, $feature);
		$this->applyParametersToOutputSettings($output_settings, $parameters, $form);
		// load customized output settings list
		$customized_list = $output_settings->getCustomSettings($feature);
		// apply conditions to automatically load output settings
		//$parameters['force'] = true; // TODO uncomment this when you create your conditional forms
		if (!isset($parameters['force'])) {
			/** @var $output_settings_list Output_Setting\Set[] */
			$output_settings_list = $output_settings->selectedSettingsToCustomSettings($customized_list);
			/** @var $new_settings Output_Setting\Set */
			$new_settings = Output_Setting\Set::conditionalOutputSettings($output_settings_list, $object);
			if (
				$new_settings
				&& ($output_settings->name != $new_settings->name)
				&& (
					!$output_settings->name
					|| !((new Code($output_settings->conditions))->execute($object, true))
				)
			) {
				$output_settings = $new_settings;
				$customized_list = $output_settings->getCustomSettings($feature);
				$output_settings->cleanup();
			}
			// go to default setting (without conditions) if current output settings condition do not apply
			elseif (!((new Code($output_settings->conditions))->execute($object, true))) {
				$output_settings = Output_Setting\Set::unconditionalOutputSettings(
					$output_settings_list, $class_name, $feature
				);
				$customized_list = $output_settings->getCustomSettings($feature);
				$output_settings->cleanup();
			}
		}

		$this->applyOutputSettings($output_settings);
		$output_settings->initProperties($this->getPropertiesList($class_name));
		$parameters['customized_lists']            = $customized_list;
		$parameters['default_title']               = ucfirst(Names::classToDisplay($class_name));
		$parameters[Parameter::PROPERTIES_FILTER]  = array_keys($output_settings->properties);
		$parameters[Parameter::PROPERTIES_TITLE]   = $output_settings->propertiesParameter('display');
		$parameters[Parameter::PROPERTIES_TOOLTIP] = $output_settings->propertiesParameter('tooltip');
		$parameters['settings']                    = $output_settings;
		$parameters['tabs']                        = $this->getTab($object, $output_settings);
		$parameters['title']                       = $output_settings->title();
		// buttons
		$parameters['custom_buttons'] = (new Setting\Buttons)->getButtons(
			'custom ' . $feature, $object, $feature //, Target::MESSAGES TODO back but dont display output
		);
		$parameters[self::GENERAL_BUTTONS] = $this->getGeneralButtons(
			$object, $parameters, $output_settings
		);
		return $parameters;
	}

	//-------------------------------------------------------------------------------- outputSettings
	/**
	 * @param $class_name string
	 * @param $feature    string
	 * @return Output_Setting\Set
	 */
	protected function outputSettings($class_name, $feature)
	{
		$settings = Output_Setting\Set::current($class_name, $feature);
		$settings->cleanup();
		return $settings;
	}

	//----------------------------------------------------------------------------------- prepareThen
	/**
	 * Prepare close link and follows links for buttons
	 *
	 * @example Call this from getGeneralButtons() :
	 * list($close_link, $follows) = $this->prepareThen($object, $parameters);
	 * Then use $close_link and $follows as needed
	 * @param $object             object|string object or class name
	 * @param $parameters         array
	 * @param $default_close_link string
	 * @return array first element is the close link, second element is an array of a link parameter
	 */
	protected function prepareThen($object, array $parameters, $default_close_link = null)
	{
		if (isset($parameters[Parameter::THEN])) {
			$close_link = $parameters[Parameter::THEN];
			$follows    = [Parameter::THEN => $parameters[Parameter::THEN]];
		}
		else {
			$close_link = $default_close_link
				?: View::link(Names::classToSet(is_object($object) ? get_class($object) : $object));
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
	 * @param $files      array[]
	 * @param $class_name string
	 * @return mixed
	 */
	public function run(Parameters $parameters, array $form, array $files, $class_name)
	{
		$parameters = $this->getViewParameters($parameters, $form, $class_name);
		return View::run($parameters, $form, $files, $class_name, Feature::F_OUTPUT);
	}

	//----------------------------------------------------------------------------- selectPrintButton
	/**
	 * Select the print button into $print_buttons which will replace the default link of
	 * $print_button
	 *
	 * @param $print_button  Button
	 * @param $print_buttons Button[]
	 */
	protected function selectPrintButton(Button $print_button, array $print_buttons)
	{
		if ($print_buttons) {
			$first_button         = reset($print_buttons);
			$print_button->link   = $first_button->link;
			$print_button->target = $first_button->target;
		}
	}

}
