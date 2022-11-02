<?php
namespace ITRocks\Framework\Feature\List_;

use ITRocks\Framework\Dao\Option\Sort;

/**
 * Sort options
 */
class Sort_Options
{

	//--------------------------------------------------------------------------------- $sort_options
	/**
	 * @var Sort[] key is class name
	 */
	private array $sort_options = [];

	//------------------------------------------------------------------------------------------- add
	/**
	 * add(string, string)
	 * Adds a sort property path for a given class name
	 *
	 * add(string, string[])
	 * Replaces sort property paths list for a given class name
	 *
	 * @param $class_name    string
	 * @param $property_path string|string[]
	 */
	public function add(string $class_name, array|string $property_path)
	{
		if (is_array($property_path)) {
			$this->sort_options[$class_name] = new Sort($property_path);
		}
		else {
			$options = $this->sort_options[$class_name] ?? null;
			if (!isset($options)) {
				$options = new Sort($class_name);
				$this->sort_options[$class_name] = $options;
			}
			if (in_array($property_path, $options->columns)) {
				unset($options->columns[array_search($property_path, $options->columns)]);
			}
			array_unshift($options->columns, $property_path);
		}
	}

	//------------------------------------------------------------------------------------------- get
	/**
	 * get(string, string)
	 * Returns true if the property path is into the sort list of the class
	 *
	 * get(string)
	 * Gets the sort property paths list for the class name
	 *
	 * @param $class_name    string
	 * @param $property_path string
	 * @return boolean|Sort
	 */
	public function get(string $class_name, string $property_path = '') : bool|Sort
	{
		if ($property_path === '') {
			return $this->sort_options[$class_name] ?? new Sort($class_name);
		}
		return $this->sort_options[$class_name]->columns[$property_path] ?? false;
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * remove(string, string)
	 * Removes a property path from the class sort list
	 *
	 * remove(string)
	 * Removes all property paths from the class sort list
	 *
	 * @param $class_name    string
	 * @param $property_path string
	 */
	public function remove(string $class_name, string $property_path = '')
	{
		if ($property_path === '') {
			if (isset($this->sort_options[$class_name])) {
				unset($this->sort_options[$class_name]);
			}
			return;
		}
		$options = $this->sort_options[$class_name] ?? null;
		if (!isset($options)) {
			return;
		}
		if (in_array($property_path, $options->columns)) {
			unset($options->columns[array_search($property_path, $options->columns)]);
		}
		if (in_array($property_path, $options->reverse)) {
			unset($options->columns[array_search($property_path, $options->reverse)]);
		}
		if (!$options->columns) {
			unset($this->sort_options[$class_name]);
		}
	}

	//--------------------------------------------------------------------------------------- reverse
	/**
	 * Reverse sort for the property path
	 *
	 * @param $class_name    string
	 * @param $property_path string
	 */
	public function reverse(string $class_name, string $property_path)
	{
		$options = $this->sort_options[$class_name] ?? null;
		if (!isset($options)) {
			return;
		}
		if (in_array($property_path, $options->reverse)) {
			unset($options->reverse[array_search($property_path, $options->reverse)]);
		}
		else {
			$options->reverse[] = $property_path;
		}
	}

}
