<?php
namespace ITRocks\Framework\Widget\Output_Setting;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Annotation\Class_\Group_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Setting\Custom_Settings;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Widget\Button;
use ITRocks\Framework\Widget\Button\Code;
use ITRocks\Framework\Widget\Tab;
use ITRocks\Framework\Widget\Tab\Tabs_Builder_Class;

/**
 * Output settings for personalized forms
 */
class Output_Settings extends Custom_Settings
{

	//-------------------------------------------------------------------------------------- $actions
	/**
	 * @var Button[]
	 */
	public $actions;

	//----------------------------------------------------------------------------------- $conditions
	/**
	 * A text php expression where $this is the referent object.
	 * The returned boolean value will tell if the form must be available or not for the object.
	 *
	 * @max_length 60000
	 * @multiline
	 * @var string
	 */
	public $conditions;

	//---------------------------------------------------------------------------------------- $title
	/**
	 * The title that will be displayed on the top of the output window
	 *
	 * @var string
	 */
	public $title;

	//----------------------------------------------------------------------------------- $properties
	/**
	 * @var Property[] key is the path of the property
	 */
	public $properties = [];

	//------------------------------------------------------------------------------------------ $tab
	/**
	 * @var Tab
	 */
	public $tab = null;

	//------------------------------------------------------------------------------------- addAction
	/**
	 * Insert a button before / after another button in the actions bar
	 *
	 * @param $button       Button
	 * @param $where        string 'after' or 'before'
	 * @param $where_action string
	 */
	public function addAction(Button $button, $where = 'after', $where_action = null)
	{
		$actions   = [];
		$done      = false;
		$insert_in = -1;
		foreach ($this->actions as $action) {
			if ($action->feature === $where_action) {
				$insert_in = ($where === 'after') ? 2 : 1;
			}
			if (!--$insert_in) {
				$actions[] = $button;
				$done = true;
			}
			$actions[] = $action;
		}
		if (!$done) {
			$actions[] = $button;
		}
		if ($button->code->source) {
			Dao::write($button->code);
		}
		$this->actions = $actions;
	}

	//----------------------------------------------------------------------------------- addProperty
	/**
	 * @param $add_property_path   string
	 * @param $tab_name            string
	 * @param $where               string 'after', 'before' or null
	 * @param $where_property_path string reference property path for $where
	 */
	public function addProperty(
	 	$add_property_path, $tab_name, $where = 'after', $where_property_path = null
	) {
		$this->initProperties();
		$add_property = isset($this->properties[$add_property_path])
			? $this->properties[$add_property_path]
			: Builder::create(Property::class, [$this->getClassName(), $add_property_path]);
		$add_property->tab_name = $tab_name;
		$properties = [];
		if (($where == 'after') && empty($where_property_path)) {
			$properties[$add_property_path] = $add_property;
		}
		foreach ($this->properties as $property_path => $property) {
			if (($where == 'before') && ($property_path == $where_property_path)) {
				$properties[$add_property_path] = $add_property;
			}
			if ($property_path !== $add_property_path) {
				$properties[$property_path] = $property;
			}
			if (($where == 'after') && ($property_path == $where_property_path)) {
				$properties[$add_property_path] = $add_property;
			}
		}
		if (($where == 'before') && empty($where_property_path)) {
			$properties[$add_property_path] = $add_property;
		}

		$this->properties = $properties;
	}

	//--------------------------------------------------------------------------------------- cleanup
	/**
	 * This cleanup method is called after loading and getting the current value
	 * in order to avoid crashes when some components of the setting disappeared in the meantime.
	 *
	 * @return integer number of changes made during cleanup : if 0, then cleanup was not necessary
	 */
	public function cleanup()
	{
		$changes_count = 0;
		foreach (array_keys($this->properties) as $property_path) {
			if (!Reflection_Property::exists($this->getClassName(), $property_path)) {
				unset($this->properties[$property_path]);
				$changes_count ++;
			}
		}
		return $changes_count;
	}

	//--------------------------------------------------------------------- conditionalOutputSettings
	/**
	 * @param $output_settings_list Output_Settings[]
	 * @param $object               object
	 * @return Output_Settings|null
	 */
	public static function conditionalOutputSettings(array $output_settings_list, $object)
	{
		foreach ($output_settings_list as $output_settings) {
			if ($output_settings->conditions) {
				$code = new Code();
				$code->source = $output_settings->conditions;
				if ($code->execute($object, true)) {
					return $output_settings;
				}
			}
		}
		return null;
	}

	//------------------------------------------------------------------------------- getDefaultTitle
	/**
	 * @return string
	 */
	private function getDefaultTitle()
	{
		return Loc::tr(ucfirst(Names::classToDisplay($this->getClassName())));
	}

	//-------------------------------------------------------------------------------- initProperties
	/**
	 * @param $filter_properties string[] property path
	 * @return Property[]
	 */
	public function initProperties(array $filter_properties = null)
	{
		if (!$this->properties) {
			$class_name = $this->getClassName();
			if ($filter_properties) {
				foreach ($filter_properties as $property_path) {
					$this->properties[$property_path] = Builder::create(
						Property::class, [$class_name, $property_path]
					);
				}
			}
			else {
				$class      = new Reflection_Class($class_name);
				$properties = $class->getProperties([T_EXTENDS, T_USE, Reflection_Class::T_SORT]);
				foreach ($properties as $property) {
					if ($property->isPublic() && !$property->isStatic()) {
						$this->properties[$property->name] = Builder::create(
							Property::class, [$class_name, $property->name]
						);
					}
				}
				foreach (Group_Annotation::allOf($class) as $group_annotation) {
					foreach ($group_annotation->values() as $property_path) {
						if (strpos($property_path, DOT)) {
							$this->properties[$property_path] = Builder::create(
								Property::class, [$class_name, $property_path]
							);
						}
					}
				}
			}
		}
		return $this->properties;
	}

	//--------------------------------------------------------------------------------------- initTab
	/** @noinspection PhpUnusedPrivateMethodInspection */
	/**
	 * TODO NORMAL in-tabs management
	 */
	private function initTab()
	{
		if (!isset($this->tab)) {
			$this->tab           = new Tab('main');
			$tabs_builder        = new Tabs_Builder_Class();
			$this->tab->includes = $tabs_builder->build($this->getClass(), array_keys($this->properties));
		}
	}

	//--------------------------------------------------------------------------- propertiesParameter
	/**
	 * Returns a list of a given parameter taken from properties
	 *
	 * @example $properties_display = $output_settings->propertiesParameter('display');
	 * @param $parameter string
	 * @return array key is the property path, value is the parameter value
	 */
	public function propertiesParameter($parameter)
	{
		$result = [];
		foreach ($this->properties as $property_path => $property) {
			$result[$property_path] = $property->$parameter;
		}
		return $result;
	}

	//----------------------------------------------------------------------------- propertyHideEmpty
	/**
	 * Sets the property to hide-empty
	 *
	 * @param $property_path string
	 * @param $hide_empty    boolean
	 */
	public function propertyHideEmpty($property_path, $hide_empty = false)
	{
		$this->initProperties();
		if (isset($this->properties[$property_path])) {
			$this->properties[$property_path]->hide_empty = $hide_empty;
		}
	}

	//------------------------------------------------------------------------------ propertyReadOnly
	/**
	 * Sets the property to read-only
	 *
	 * @param $property_path string
	 * @param $read_only     boolean
	 */
	public function propertyReadOnly($property_path, $read_only = false)
	{
		$this->initProperties();
		if (isset($this->properties[$property_path])) {
			$this->properties[$property_path]->read_only = $read_only;
		}
	}

	//--------------------------------------------------------------------------------- propertyTitle
	/**
	 * Sets the title of the property
	 *
	 * @param $property_path string
	 * @param $title         string if empty or null, the title is removed to get back to default
	 */
	public function propertyTitle($property_path, $title = null)
	{
		$this->initProperties();
		if (isset($this->properties[$property_path])) {
			$this->properties[$property_path]->display = $title;
		}
	}

	//---------------------------------------------------------------------------------- removeAction
	/**
	 * Removes an action using this caption (as this is the only sure unique data)
	 *
	 * @param $caption string
	 * @return boolean true if the action has been removed, false if it was not found
	 */
	public function removeAction($caption)
	{
		if ($this->actions) {
			foreach ($this->actions as $key => $action) {
				if ($action->caption == $caption) {
					unset($this->actions[$key]);
					return true;
				}
			}
		}
		return false;
	}

	//-------------------------------------------------------------------------------- removeProperty
	/**
	 * @param $property_path string
	 */
	public function removeProperty($property_path)
	{
		$this->initProperties();
		if (isset($this->properties[$property_path])) {
			unset($this->properties[$property_path]);
		}
	}

	//----------------------------------------------------------------------------------------- title
	/**
	 * @param $title string
	 * @return string
	 */
	public function title($title = null)
	{
		if (isset($title)) {
			$this->title = $title;
		}
		return empty($this->title) ? $this->getDefaultTitle() : $this->title;
	}

	//------------------------------------------------------------------- unconditionalOutputSettings
	/**
	 * @param $output_settings_list Output_Settings[]
	 * @param $class_name           string
	 * @param $feature              string
	 * @return Output_Settings|null
	 */
	public static function unconditionalOutputSettings(
		array $output_settings_list, $class_name, $feature
	) {
		foreach ($output_settings_list as $output_settings) {
			if (!$output_settings->conditions) {
				return $output_settings;
			}
		}
		return Output_Settings::load($class_name, $feature, '');
	}

}
