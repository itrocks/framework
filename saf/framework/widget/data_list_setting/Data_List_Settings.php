<?php
namespace SAF\Framework\Widget\Data_List_Setting;

use SAF\Framework\Setting\Custom_Settings;
use SAF\Framework\Dao\Option\Sort;
use SAF\Framework\Reflection\Reflection_Class;
use SAF\Framework\Reflection\Reflection_Property;
use SAF\Framework\Setting;
use SAF\Framework\Tools\Names;

/**
 * Data list settings : all that can be customized into a list view
 */
class Data_List_Settings extends Custom_Settings
{

	//---------------------------------------------------------------------------------------- $title
	/**
	 * The title that will be displayed on the top of the list
	 *
	 * @var string
	 */
	public $title;

	//------------------------------------------------------------------------------ $properties_path
	/**
	 * Properties path
	 *
	 * @var string[] key is the column number (0..n)
	 */
	public $properties_path;

	//----------------------------------------------------------------------------- $properties_title
	/**
	 * Properties title
	 *
	 * @var string[] key is the property path
	 */
	public $properties_title = [];

	//--------------------------------------------------------------------------------------- $search
	/**
	 * Search criterion
	 *
	 * @var string[] key is the property path, value is the value or search expression
	 */
	public $search = [];

	//----------------------------------------------------------------------------------------- $sort
	/**
	 * Sort option (sort properties and reverse)
	 *
	 * @var Sort
	 */
	public $sort;

	//---------------------------------------------------------------- $maximum_displayed_lines_count
	/**
	 * Maximum displayed lines count
	 *
	 * @var integer
	 */
	public $maximum_displayed_lines_count = 20;

	//-------------------------------------------------------------------- $start_display_line_number
	/**
	 * @var integer
	 */
	public $start_display_line_number = 1;

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
		if (!isset($this->sort)) {
			$this->sort = new Sort($class_name);
		}
		if (!isset($this->properties_path) && isset($this->class_name)) {
			$this->properties_path = (new Reflection_Class($this->class_name))
				->getListAnnotation('representative')->values();
		}
	}

	//----------------------------------------------------------------------------------- addProperty
	/**
	 * @param $property_path       string
	 * @param $where               string 'after', 'before' or null
	 * @param $where_property_path string reference property path for $where
	 */
	public function addProperty($property_path, $where = 'after', $where_property_path = null)
	{
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
	 * Cleanup outdated properties from the list setting
	 *
	 * @return integer number of changes made during cleanup : if 0, then cleanup was not necessary
	 */
	public function cleanup()
	{
		$changes_count = 0;
		// properties path
		foreach ($this->properties_path as $key => $property_path) {
			if (!Reflection_Property::exists($this->class_name, $property_path)) {
				unset($this->properties_path[$key]);
				$changes_count ++;
			}
		}
		if ($changes_count) {
			$this->properties_path = array_values($this->properties_path);
		}
		// search
		foreach (array_keys($this->search) as $property_path) {
			if (!Reflection_Property::exists($this->class_name, $property_path)) {
				unset($this->search[$property_path]);
				$changes_count ++;
			}
		}
		// sort
		if ($this->sort) foreach ($this->sort->columns as $key => $property_path) {
			if (!Reflection_Property::exists($this->class_name, $property_path)) {
				unset($this->sort->columns[$key]);
				$changes_count ++;
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
		return ucfirst(Names::classToDisplay(
			(new Reflection_Class($this->class_name))->getAnnotation('set')
		));
	}

	//------------------------------------------------------------------------------------------ load
	/**
	 * Loads a Data_List_Settings from the Settings set
	 *
	 * If no Data_List_Settings named $name is stored, a new one will be returned
	 *
	 * @param $class_name string
	 * @param $name       string
	 * @return Data_List_Settings
	 */
	public static function load($class_name, $name)
	{
		return parent::load($class_name, $name);
	}

	//-------------------------------------------------------------------------------- removeProperty
	/**
	 * @param $property_path string
	 */
	public function removeProperty($property_path)
	{
		if (($key = array_search($property_path, $this->properties_path, true)) !== false) {
			unset($this->properties_path[$key]);
			$this->properties_path = array_values($this->properties_path);
		}
	}

	//--------------------------------------------------------------------------------------- reverse
	/**
	 * @param $property_path string
	 */
	public function reverse($property_path)
	{
		if (in_array($property_path, $this->sort->reverse)) {
			unset($this->sort->reverse[array_search($property_path, $this->sort->reverse)]);
		}
		else {
			array_push($this->sort->reverse, $property_path);
		}
	}

	//------------------------------------------------------------------------------------------ sort
	/**
	 * @param $property_path string
	 */
	public function sort($property_path)
	{
		$this->sort->addSortColumn($property_path);
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

	//---------------------------------------------------------------------------------------- search
	/**
	 * Adds search values
	 *
	 * If a search value is empty, the search value is removed
	 * Already existing search values for other properties path stay unchanged
	 *
	 * @param $search array key is the property path
	 */
	public function search($search)
	{
		foreach ($search as $property_path => $value) {
			if (!strlen($value)) {
				if (isset($this->search[$property_path])) {
					unset($this->search[$property_path]);
				}
			}
			else {
				$this->search[$property_path] = $value;
			}
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
