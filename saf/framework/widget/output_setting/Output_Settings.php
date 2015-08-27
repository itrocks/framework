<?php
namespace SAF\Framework\Widget\Output_Setting;

use SAF\Framework\Reflection\Reflection_Class;
use SAF\Framework\Reflection\Reflection_Property;
use SAF\Framework\Setting;
use SAF\Framework\Setting\Custom_Settings;
use SAF\Framework\Tools\Names;
use SAF\Framework\Widget\Tab;
use SAF\Framework\Widget\Tab\Tabs_Builder_Class;

/**
 * Output settings for personalized forms
 */
class Output_Settings extends Custom_Settings
{

	//---------------------------------------------------------------------------------------- $title
	/**
	 * The title that will be displayed on the top of the output window
	 *
	 * @var string
	 */
	public $title;

	//------------------------------------------------------------------------------ $properties_path
	/**
	 * Properties path
	 *
	 * @var string[] key is the sort index (0..n)
	 */
	public $properties_path = null;

	//----------------------------------------------------------------------------- $properties_title
	/**
	 * Properties title
	 *
	 * @var string[] key is the property path
	 */
	public $properties_title = [];

	//------------------------------------------------------------------------------------------ $tab
	/**
	 * @var Tab
	 */
	public $tab = null;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name string
	 * @param $setting    Setting
	 */
	public function __construct($class_name = null, Setting $setting = null)
	{
		if (isset($class_name)) {
			$this->class_name = $class_name;
		}
		if (isset($setting)) {
			$this->setting = $setting;
		}
	}

	//----------------------------------------------------------------------------------- addProperty
	/**
	 * @param $property_path       string
	 * @param $tab_name            string
	 * @param $where               string 'after', 'before' or null
	 * @param $where_property_path string reference property path for $where
	 */
	public function addProperty(
	 	$property_path, $tab_name, $where = 'after', $where_property_path = null
	) {
		$this->initPropertiesPath();
		$properties_path = [];
		$count = 0;
		if (($where == 'after') && empty($where_property_path)) {
			$properties_path[$count++] = $property_path;
		}
		foreach ($this->properties_path as $key) {
			if (($where == 'before') && ($key == $where_property_path)) {
				$properties_path[$count++] = $property_path;
			}
			if ($key !== $property_path) {
				$properties_path[$count++] = $key;
			}
			if (($where == 'after') && ($key == $where_property_path)) {
				$properties_path[$count++] = $property_path;
			}
		}
		if (($where == 'before') && empty($where_property_path)) {
			$properties_path[$count] = $property_path;
		}

		$this->properties_path = $properties_path;
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
		// properties path
		if (isset($this->properties_path)) {
			foreach ($this->properties_path as $key => $property_path) {
				if (!Reflection_Property::exists($this->class_name, $property_path)) {
					unset($this->properties_path[$key]);
					$changes_count++;
				}
			}
			if ($changes_count) {
				$this->properties_path = array_values($this->properties_path);
			}
		}
		return $changes_count;
	}

	//--------------------------------------------------------------------------------------- current
	/**
	 * Get current session / user custom settings object
	 *
	 * @param $class_name string
	 * @return self
	 */
	public static function current($class_name)
	{
		return parent::current($class_name);
	}

	//------------------------------------------------------------------------------- getDefaultTitle
	/**
	 * @return string
	 */
	private function getDefaultTitle()
	{
		return ucfirst(Names::classToDisplay($this->class_name));
	}

	//---------------------------------------------------------------------------- initPropertiesPath
	private function initPropertiesPath()
	{
		if (!$this->properties_path) {
			$this->properties_path = array_keys(
				(new Reflection_Class($this->class_name))->getProperties([T_EXTENDS, T_USE])
			);
		}
	}

	//--------------------------------------------------------------------------------------- initTab
	private function initTab()
	{
		if (!isset($this->tab) && isset($this->class_name)) {
			$this->tab = new Tab('main');
			$this->tab->includes = Tabs_Builder_Class::build(
				new Reflection_Class($this->class_name), $this->properties_path
			);
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
		if (empty($title)) {
			if (isset($this->properties_title[$property_path])) {
				unset($this->properties_title[$property_path]);
			}
		}
		else {
			$this->properties_title[$property_path] = $title;
		}
	}

	//-------------------------------------------------------------------------------- removeProperty
	/**
	 * @param $property_path string
	 */
	public function removeProperty($property_path)
	{
		$this->initPropertiesPath();
		if (($key = array_search($property_path, $this->properties_path, true)) !== false) {
			unset($this->properties_path[$key]);
			$this->properties_path = array_values($this->properties_path);
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
		return empty($this->title)
			? $this->getDefaultTitle()
			: $this->title;
	}

}
