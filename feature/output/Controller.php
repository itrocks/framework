<?php
namespace ITRocks\Framework\Feature\Output;

use ITRocks\Framework\Component\Button;
use ITRocks\Framework\Component\Button\Align;
use ITRocks\Framework\Component\Button\Code;
use ITRocks\Framework\Component\Button\Has_General_Buttons;
use ITRocks\Framework\Component\Menu;
use ITRocks\Framework\Component\Tab;
use ITRocks\Framework\Component\Tab\Tabs_Builder_Object;
use ITRocks\Framework\Controller\Default_Feature_Controller;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Parameter;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Feature\Duplicate;
use ITRocks\Framework\Feature\List_\Navigation;
use ITRocks\Framework\Feature\Output_Setting;
use ITRocks\Framework\Layout\Print_Model\Buttons_Generator;
use ITRocks\Framework\Reflection\Annotation\Property\Group_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\User_Annotation;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Session;
use ITRocks\Framework\Setting;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\View;

/**
 * All output controllers should extend from this at it offers standard output elements methods and structure
 *
 * @feature @built_in Output your business objects and documents into standard views
 */
class Controller implements Default_Feature_Controller, Has_General_Buttons
{

	//--------------------------------------------------------------------------------------- FEATURE
	const FEATURE = Feature::F_OUTPUT;

	//------------------------------------------------------------------------------- HIDE_EMPTY_TEST
	/**
	 * Parameter for Reflection_Property::isVisible (for tabs)
	 */
	const HIDE_EMPTY_TEST = true;

	//---------------------------------------------------------------------------------- alignButtons
	/**
	 * @param $buttons Button[]
	 */
	protected function alignButtons(array &$buttons)
	{
		$found = [Align::LEFT => true, Align::CENTER => false, Align::RIGHT => false];
		$more  = [Align::LEFT => [],   Align::CENTER => [],    Align::RIGHT => []];
		foreach ($buttons as $key => $button) {
			if (!$button->align) {
				continue;
			}
			unset($buttons[$key]);
			$more[$button->align][$key] = $button;
			// patch for css : only the first button will get data-align="right"
			if (!$found[$button->align]) {
				$found[$button->align] = true;
				continue;
			}
			$button->align = '';
		}
		$buttons = array_merge($more[Align::LEFT], $buttons, $more[Align::CENTER], $more[Align::RIGHT]);
	}

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
	 * @param $form            array|null
	 * @return ?Output_Setting\Set set if parameters did change
	 */
	public function applyParametersToOutputSettings(
		Output_Setting\Set &$output_settings, array $parameters, array $form = null
	) : ?Output_Setting\Set
	{
		if (isset($form)) {
			$parameters = array_merge($parameters, $form);
		}
		$did_change = $parameters[Parameter::DID_CHANGE] ?? false;
		if (isset($parameters['add_action'])) {
			if (!$output_settings->actions) {
				$output_settings->actions = $this->getGeneralButtons(
					$output_settings->getClassName(), $parameters
				);
			}
			$output_settings->addAction(
				$parameters['add_action'],
				isset($parameters['before']) ? 'before' : 'after',
				$parameters['before'] ?? $parameters['after']
			);
			$did_change = true;
		}
		if (isset($parameters['add_property'])) {
			$output_settings->addProperty(
				$parameters['add_property'],
				$parameters['tab'] ?? '',
				isset($parameters['before']) ? 'before' : 'after',
				$parameters['before'] ?? ($parameters['after'] ?? '')
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
		if (isset($parameters['title'])) {
			$output_settings->name  = $parameters['title'];
			$output_settings->title = $parameters['title'];
			$did_change = true;
		}
		if (Setting\Custom\Controller::applyParametersToCustomSettings($output_settings, $parameters)) {
			$did_change = true;
		}
		if ($did_change) {
			$output_settings->save($parameters['title'] ?? null);
		}
		return $did_change ? $output_settings : null;
	}

	//----------------------------------------------------------------------------- getGeneralButtons
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @noinspection PhpDocSignatureInspection $settings
	 * @param $object     object|string object or class name
	 * @param $parameters array parameters
	 * @param $settings   Setting\Custom\Set&Output_Setting\Set|null
	 * @return Button[]
	 */
	public function getGeneralButtons(
		object|string $object, array $parameters, Setting\Custom\Set $settings = null
	) : array
	{
		[$close_link, $follows]    = $this->prepareThen($object, $parameters);
		$buttons[Feature::F_CLOSE] = new Button(
			'Close', $close_link, Feature::F_CLOSE
		);
		$buttons[Feature::F_EDIT] = new Button(
			'Edit', View::link($object, Feature::F_EDIT, null, $follows), Feature::F_EDIT
		);
		if ($object instanceof Duplicate) {
			$buttons[Feature::F_DUPLICATE] = new Button(
				'Duplicate', View::link($object, Feature::F_DUPLICATE, null, $follows), Feature::F_DUPLICATE
			);
		}

		$layout_model_buttons      = (new Buttons_Generator($object))->getButtons();
		$buttons[Feature::F_PRINT] = new Button(
			'Print',
			View::link($object, Feature::F_PRINT),
			Feature::F_PRINT
		);
		$this->selectPrintButton($buttons[Feature::F_PRINT], $layout_model_buttons);

		if (Dao::getObjectIdentifier($object)) {
			$buttons[Feature::F_DELETE] = new Button(
				'Delete',
				View::link($object, Feature::F_DELETE, null, $follows),
				Feature::F_DELETE,
				Target::RESPONSES
			);
		}

		if (is_object($object) && Dao::getObjectIdentifier($object)) {
			$buttons['outputPrevious'] = new Button(
				Feature::F_OUTPUT,
				View::link($object, Feature::F_OUTPUT, 'previous'),
				'outputPrevious',
				[Align::RIGHT, Target::MAIN]
			);
			$buttons['outputNext'] = new Button(
				Feature::F_OUTPUT,
				View::link($object, Feature::F_OUTPUT, 'next'),
				'outputNext',
				[Align::RIGHT, Target::MAIN]
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

	//------------------------------------------------------------------------------------- getModule
	/**
	 * @param $class_name string
	 * @return Button|string
	 */
	protected function getModule(string $class_name) : Button|string
	{
		$class_names = Names::classToSet($class_name);
		$module      = '';
		if (!Menu::registered()) {
			return $module;
		}
		$menu = Menu::get();
		foreach ([$class_names, $class_name] as $link_class_name) {
			foreach ($menu->blocks as $block) {
				foreach ($block->items as $item) {
					if (str_starts_with($item->link, View::link($link_class_name))) {
						$module = new Button($block->title, $block->title_link);
						break 3;
					}
				}
			}
		}
		return $module;
	}

	//------------------------------------------------------------------------------------- getParent
	/**
	 * @param $class_name string
	 * @return Button|string
	 */
	protected function getParent(string $class_name) : Button|string
	{
		$class_names = Names::classToSet($class_name);
		$parent = '';
		if (!Menu::registered()) {
			return $parent;
		}
		$menu = Menu::get();
		foreach ([$class_names, $class_name] as $link_class_name) {
			foreach ($menu->blocks as $block) {
				foreach ($block->items as $item) {
					if (str_starts_with($item->link, View::link($link_class_name))) {
						$parent = new Button($item->caption, $item->link);
						break 3;
					}
				}
			}
		}
		return $parent;
	}

	//----------------------------------------------------------------------------- getPropertiesList
	/**
	 * @param $class_name string
	 * @return string[] property names list
	 */
	protected function getPropertiesList(
		/** @noinspection PhpUnusedParameterInspection */ string $class_name
	) : array {
		return [];
	}

	//---------------------------------------------------------------------------------------- getTab
	/**
	 * Get output tab for a given object
	 *
	 * @param $object          object
	 * @param $output_settings Output_Setting\Set
	 * @return Tab
	 */
	protected function getTab(object $object, Output_Setting\Set $output_settings) : Tab
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
	 * @return array
	 */
	protected function getViewParameters(Parameters $parameters, array $form, string $class_name)
		: array
	{
		$object     = $parameters->getMainObject($class_name);
		$parameters = $parameters->getObjects();
		$feature    = $parameters[Feature::FEATURE] ?? static::FEATURE;

		// apply parameters / form to current output settings
		$output_settings = $this->outputSettings($class_name, $feature);
		$this->applyParametersToOutputSettings($output_settings, $parameters, $form);
		// load customized output settings list
		$customized_list = $output_settings->getCustomSettings($feature);
		// apply conditions to automatically load output settings
		//$parameters['force'] = true; // TODO uncomment this when you create your conditional forms
		if (!isset($parameters['force'])) {
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
		$parameters['is_output']                   = ($feature === Feature::F_OUTPUT);
		$parameters[Parameter::PROPERTIES_FILTER]  = array_keys($output_settings->properties);
		$parameters[Parameter::PROPERTIES_TITLE]   = $output_settings->propertiesParameter('display');
		$parameters[Parameter::PROPERTIES_TOOLTIP] = $output_settings->propertiesParameter('tooltip');
		$parameters['module']                      = $this->getModule($class_name);
		$parameters['parent']                      = $this->getParent($class_name);
		$parameters['settings']                    = $output_settings;
		$parameters['tabs']                        = $this->getTab($object, $output_settings);
		$parameters['title']                       = $output_settings->title();
		// buttons
		$parameters['custom_buttons'] = (new Setting\Buttons)->getButtons(
			'custom ' . $feature, $object, $feature //, Target::RESPONSES TODO back but dont display output
		);
		$parameters[self::GENERAL_BUTTONS] = $this->getGeneralButtons(
			$object, $parameters, $output_settings
		);
		$this->alignButtons($parameters[self::GENERAL_BUTTONS]);
		if (isset($parameters['only'])) {
			$this->onlyProperties(
				$object, $parameters[Parameter::PROPERTIES_FILTER], Parameters::toArray($parameters['only'])
			);
		}
		return $parameters;
	}

	//-------------------------------------------------------------------------------- onlyProperties
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object            object
	 * @param $properties_filter string[]
	 * @param $only              string[]
	 */
	protected function onlyProperties(object $object, array &$properties_filter, array $only)
	{
		$auto = [];
		foreach ($only as $key => $property_name) {
			unset($only[$key]);
			if ($property_name[0] === '@') {
				$auto[$property_name] = true;
			}
		}
		if ($only) {
			$properties_filter = array_intersect($properties_filter, $only);
		}
		if (!$auto) {
			return;
		}
		$properties = [];
		foreach ($properties_filter as $property_name) {
			/** @noinspection PhpUnhandledExceptionInspection must exist */
			$properties[$property_name] = new Reflection_Property($object, $property_name);
		}
		$this->onlyPropertiesAuto($properties_filter, $auto, $properties);
	}

	//---------------------------------------------------------------------------- onlyPropertiesAuto
	/**
	 * @param $properties_filter string[]
	 * @param $auto              string[]
	 * @param $properties        Reflection_Property[]
	 */
	public function onlyPropertiesAuto(array &$properties_filter, array $auto, array $properties)
	{
		if (isset($auto['@modifiable'])) {
			foreach ($properties_filter as $key => $property_name) {
				if (User_Annotation::of($properties[$property_name])->isModifiable()) {
					continue;
				}
				unset($properties_filter[$key]);
			}
		}
	}

	//-------------------------------------------------------------------------------- outputSettings
	/**
	 * @param $class_name string
	 * @param $feature    string
	 * @return Output_Setting\Set
	 */
	protected function outputSettings(string $class_name, string $feature) : Output_Setting\Set
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
	 * [$close_link, $follows] = $this->prepareThen($object, $parameters);
	 * Then use $close_link and $follows as needed
	 * @param $object             object|string object or class name
	 * @param $parameters         array
	 * @param $default_close_link string|null
	 * @return array first element is the close link, second element is an array of a link parameter
	 */
	protected function prepareThen(
		object|string $object, array $parameters, string $default_close_link = null
	) : array
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
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @param $class_name string
	 * @return ?string
	 */
	public function run(Parameters $parameters, array $form, array $files, string $class_name)
		: ?string
	{
		if ($parameters->has('next', true) && Dao::getObjectIdentifier($parameters->getMainObject())) {
			/** @noinspection PhpUnhandledExceptionInspection class */
			Session::current()->get(Navigation::class, true)->navigate($parameters, 1);
		}
		elseif (
			$parameters->has('previous', true) && Dao::getObjectIdentifier($parameters->getMainObject())
		) {
			/** @noinspection PhpUnhandledExceptionInspection class */
			Session::current()->get(Navigation::class, true)->navigate($parameters, -1);
		}
		$parameters = $this->getViewParameters($parameters, $form, $class_name);
		return View::run($parameters, $form, $files, $class_name, static::FEATURE);
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
