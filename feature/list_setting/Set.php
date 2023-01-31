<?php
namespace ITRocks\Framework\Feature\List_Setting;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao\Option\Sort;
use ITRocks\Framework\Reflection\Annotation\Class_\List_Annotation;
use ITRocks\Framework\Reflection\Attribute\Class_\Displays;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Setting;
use ITRocks\Framework\Setting\Has_Properties;
use ReflectionException;

/**
 * Data list settings : all that can be customized into a list view
 *
 * @override $properties Property[]
 * @property Property[] $properties
 */
class Set extends Setting\Custom\Set
{
	use Has_Properties;

	//--------------------------------------------------------------------------------- AFTER, BEFORE
	const AFTER  = 'after';
	const BEFORE = 'before';

	//---------------------------------------------------------------- $maximum_displayed_lines_count
	/**
	 * Maximum displayed lines count is the number of displayed lines on lists
	 * 0 means undefined (=> take default value back)
	 *
	 * @var integer
	 */
	public int $maximum_displayed_lines_count = 20;

	//----------------------------------------------------------------------------------- $properties
	/**
	 * Custom properties used for columns into the list
	 *
	 * @var Property[] key is the path of the property
	 */
	public array $properties = [];

	//--------------------------------------------------------------------------------------- $search
	/**
	 * Search criteria
	 *
	 * @var string[] key is the property path, value is the value or search expression
	 */
	public array $search = [];

	//----------------------------------------------------------------------------------------- $sort
	/**
	 * Sort option (sort properties and reverse)
	 *
	 * @var Sort
	 */
	public Sort $sort;

	//-------------------------------------------------------------------- $start_display_line_number
	/**
	 * @var integer
	 */
	public int $start_display_line_number = 1;

	//---------------------------------------------------------------------------------------- $title
	/**
	 * The title that will be displayed on the top of the list
	 *
	 * @var string
	 */
	public string $title = '';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name string|null
	 * @param $setting    Setting|null
	 */
	public function __construct(string $class_name = null, Setting $setting = null)
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
	 * @param $where               string @values static::const,
	 * @param $where_property_path string reference property path for $where
	 */
	public function addProperty(
		string $add_property_path, string $where = self::AFTER, string $where_property_path = ''
	) : void
	{
		$this->initProperties();
		$this->commonAddProperty($add_property_path, $where, $where_property_path);
	}

	//--------------------------------------------------------------------------------------- cleanup
	/**
	 * Cleanup outdated properties and invisible properties from the list setting
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return integer number of changes made during cleanup : if 0, then cleanup was not necessary
	 */
	public function cleanup() : int
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
		$this->sort->class_name = Builder::className($this->sort->class_name);
		foreach ($this->sort->columns as $key => $property_path) {
			if (
				!isset($this->properties[$property_path])
				|| !Reflection_Property::exists($class_name, $property_path)
			) {
				unset($this->sort->columns[$key]);
				$changes_count ++;
			}
		}
		if ($this->maximum_displayed_lines_count < 10) {
			$this->maximum_displayed_lines_count = 10;
		}

		return $changes_count;
	}

	//------------------------------------------------------------------------------- getDefaultTitle
	/**
	 * @return string
	 */
	private function getDefaultTitle() : string
	{
		return ucfirst(Displays::of($this->getClass())->value);
	}

	//-------------------------------------------------------------------------------- initProperties
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $filter_properties string[] property path
	 * @return Property[]
	 */
	public function initProperties(array $filter_properties = []) : array
	{
		if ($this->commonInitProperties($filter_properties)) {
			return $this->properties;
		}
		$class_name = $this->getClassName();
		/** @noinspection PhpUnhandledExceptionInspection valid $class_name */
		foreach (
			List_Annotation::of(new Reflection_Class($class_name))->properties as $property_name
		) {
			try {
				$property = new Reflection_Property($class_name, $property_name);
			}
			catch (ReflectionException) {
				continue;
			}
			if ($property->isStatic() || !$property->isPublic()) {
				continue;
			}
			/** @noinspection PhpUnhandledExceptionInspection ::class */
			$this->properties[$property->path] = Builder::create(
				Property::class, [$class_name, $property->path]
			);
		}
		return $this->properties;
	}

	//------------------------------------------------------------------------------- propertyGroupBy
	/**
	 * Sets the property group by setting
	 *
	 * @param $property_path string
	 * @param $group_by      boolean
	 */
	public function propertyGroupBy(string $property_path, bool $group_by = false) : void
	{
		$this->initProperties();
		if (isset($this->properties[$property_path])) {
			$this->properties[$property_path]->group_by = $group_by;
		}
	}

	//----------------------------------------------------------------------------------- resetSearch
	/**
	 * Reset search criterion
	 */
	public function resetSearch() : void
	{
		$this->search = [];
	}

	//--------------------------------------------------------------------------------------- reverse
	/**
	 * @param $property_path string
	 */
	public function reverse(string $property_path) : void
	{
		$this->sort($property_path);
		if (!in_array($property_path, $this->sort->reverse)) {
			$this->sort->reverse[] = $property_path;
		}
	}

	//------------------------------------------------------------------------------------------ save
	/**
	 * In all cases : saves the Setting\Custom\Set object for current user and session
	 * If $save_name is set : saves the Setting\Custom\Set object into the Settings set
	 *
	 * @param $save_name string
	 */
	public function save(string $save_name = '') : void
	{
		$this->sort->class_name = Builder::current()->sourceClassName($this->sort->class_name);
		parent::save($save_name);
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
	public function search(array $search) : void
	{
		foreach ($search as $property_path => $value) {
			if (strval($value) === '') {
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
	public function sort(string $property_path) : void
	{
		$this->sort->addSortColumn($property_path);
		if (in_array($property_path, $this->sort->reverse, true)) {
			unset($this->sort->reverse[array_search($property_path, $this->sort->reverse, true)]);
		}
	}

	//----------------------------------------------------------------------------------------- title
	/**
	 * @param $title string
	 * @return string
	 */
	public function title(string $title = '') : string
	{
		if ($title) {
			$this->title = $title;
		}
		return $this->title ?: $this->getDefaultTitle();
	}

}
