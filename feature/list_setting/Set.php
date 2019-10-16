<?php
namespace ITRocks\Framework\Feature\List_Setting;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao\Option\Sort;
use ITRocks\Framework\Reflection\Annotation\Class_\Displays_Annotation;
use ITRocks\Framework\Reflection\Annotation\Class_\List_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Setting;

/**
 * Data list settings : all that can be customized into a list view
 */
class Set extends Setting\Custom\Set
{

	//---------------------------------------------------------------- $maximum_displayed_lines_count
	/**
	 * Maximum displayed lines count is the number of displayed lines on lists
	 *
	 * @var integer
	 */
	public $maximum_displayed_lines_count = 20;

	//----------------------------------------------------------------------------------- $properties
	/**
	 * Custom properties used for columns into the list
	 *
	 * @var Property[] key is the path of the property
	 */
	public $properties = [];

	//------------------------------------------------------------------------------ $properties_path
	/**
	 * Properties path
	 *
	 * @deprecated stored into $property
	 * @var string[] key is the column number (0..n)
	 */
	public $properties_path;

	//----------------------------------------------------------------------------- $properties_title
	/**
	 * Properties title
	 *
	 * @deprecated stored into $property
	 * @var string[] key is the property path
	 */
	public $properties_title = [];

	//--------------------------------------------------------------------------------------- $search
	/**
	 * Search criteria
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

	//-------------------------------------------------------------------- $start_display_line_number
	/**
	 * @var integer
	 */
	public $start_display_line_number = 1;

	//---------------------------------------------------------------------------------------- $title
	/**
	 * The title that will be displayed on the top of the list
	 *
	 * @var string
	 */
	public $title;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name string
	 * @param $setting    Setting
	 */
	public function __construct($class_name = null, Setting $setting = null)
	{
		parent::__construct($class_name, $setting);
		if (!isset($this->sort)) {
			$this->sort = new Sort($class_name);
		}
	}

	//----------------------------------------------------------------------------------- addProperty
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $add_property_path   string
	 * @param $where               string 'after', 'before' or null
	 * @param $where_property_path string reference property path for $where
	 */
	public function addProperty($add_property_path, $where = 'after', $where_property_path = null)
	{
		$this->initProperties();
		/** @noinspection PhpUnhandledExceptionInspection ::class */
		$add_property = isset($this->properties[$add_property_path])
			? $this->properties[$add_property_path]
			: Builder::create(Property::class, [$this->getClassName(), $add_property_path]);
		$properties = [];
		if (($where == 'before') && empty($where_property_path)) {
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
		if (($where == 'after') && empty($where_property_path)) {
			$properties[$add_property_path] = $add_property;
		}

		$this->properties = $properties;
	}

	//--------------------------------------------------------------------------------------- cleanup
	/**
	 * Cleanup outdated properties and invisible properties from the list setting
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return integer number of changes made during cleanup : if 0, then cleanup was not necessary
	 */
	public function cleanup()
	{
		$this->initProperties();
		$class_name    = $this->getClassName();
		$changes_count = 0;
		// properties
		foreach (array_keys($this->properties) as $property_path) {
			/** @noinspection PhpUnhandledExceptionInspection tested with exists */
			$reflection_property = (Reflection_Property::exists($class_name, $property_path))
				? new Reflection_Property($class_name, $property_path) : null;
			if (
				!$reflection_property
				|| !$reflection_property->isPublic()
				|| !$reflection_property->isVisible(false, false)
			) {
				unset($this->properties[$property_path]);
				$changes_count ++;
			}
		}
		// search
		foreach (array_keys($this->search) as $property_path) {
			if (!Reflection_Property::exists($class_name, $property_path)) {
				unset($this->search[$property_path]);
				$changes_count ++;
			}
		}
		// sort
		if ($this->sort) foreach ($this->sort->columns as $key => $property_path) {
			if (!Reflection_Property::exists($class_name, $property_path)) {
				unset($this->sort->columns[$key]);
				$changes_count ++;
			}
		}

		return $changes_count;
	}

	//------------------------------------------------------------------------------- getDefaultTitle
	/**
	 * @return string
	 */
	private function getDefaultTitle()
	{
		return ucfirst(Displays_Annotation::of($this->getClass())->value);
	}

	//-------------------------------------------------------------------------------- initProperties
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $filter_properties string[] property path
	 * @return Property[]
	 */
	public function initProperties(array $filter_properties = null)
	{
		$class_name = $this->getClassName();

		// TODO LOW this keeps compatibility with deprecated properties_path and properties_title
		if (isset($this->properties_path) && (!isset($this->properties) || !$this->properties)) {
			foreach ($this->properties_path as $property_path) {
				/** @noinspection PhpUnhandledExceptionInspection class and property must be valid */
				$property = new Property($class_name, $property_path);
				if (isset($this->properties_title[$property_path])) {
					$property->display = $this->properties_title[$property_path];
				}
				$property->path                   = $property_path;
				$this->properties[$property_path] = $property;
			}
			unset($this->properties_path);
			unset($this->properties_title);
		}

		if (!$this->properties) {
			if ($filter_properties) {
				foreach ($filter_properties as $property_path) {
					/** @noinspection PhpUnhandledExceptionInspection ::class */
					$this->properties[$property_path] = Builder::create(
						Property::class, [$class_name, $property_path]
					);
				}
			}
			else {
				/** @noinspection PhpUnhandledExceptionInspection valid $class_name */
				foreach (
					List_Annotation::of(new Reflection_Class($class_name))->properties as $property_name
				) {
					/** @noinspection PhpUnhandledExceptionInspection valid $class_name::$property_name */
					$property = new Reflection_Property($class_name, $property_name);
					if ($property->isPublic() && !$property->isStatic()) {
						/** @noinspection PhpUnhandledExceptionInspection ::class */
						$this->properties[$property->path] = Builder::create(
							Property::class, [$class_name, $property->path]
						);
					}
				}
			}
		}
		return $this->properties;
	}

	//--------------------------------------------------------------------------- propertiesParameter
	/**
	 * Returns a list of a given parameter taken from properties
	 *
	 * @example $properties_display = $list_settings->propertiesParameter('group_by');
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

	//------------------------------------------------------------------------------- propertyGroupBy
	/**
	 * Sets the property group by setting
	 *
	 * @param $property_path string
	 * @param $group_by      boolean
	 */
	public function propertyGroupBy($property_path, $group_by = false)
	{
		$this->initProperties();
		if (isset($this->properties[$property_path])) {
			$this->properties[$property_path]->group_by = $group_by;
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
		// TODO check what happens if an empty title is set : must be stored as empty, with default view
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

	//----------------------------------------------------------------------------------- resetSearch
	/**
	 * Reset search criterion
	 */
	public function resetSearch()
	{
		$this->search = [];
	}

	//--------------------------------------------------------------------------------------- reverse
	/**
	 * @param $property_path string
	 */
	public function reverse($property_path)
	{
		if (!in_array($property_path, $this->sort->reverse)) {
			$this->sort->reverse[] = $property_path;
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
	public function search(array $search)
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

	//------------------------------------------------------------------------------------------ sort
	/**
	 * @param $property_path string
	 */
	public function sort($property_path)
	{
		$this->sort->addSortColumn($property_path);
		if (in_array($property_path, $this->sort->reverse)) {
			unset($this->sort->reverse[array_search($property_path, $this->sort->reverse)]);
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
