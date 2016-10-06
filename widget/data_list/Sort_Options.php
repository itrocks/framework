<?php
namespace SAF\Framework\Widget\Data_List;

use SAF\Framework\Dao\Option\Sort;

/**
 * Sort options
 */
class Sort_Options
{

	//--------------------------------------------------------------------------------- $sort_options
	/**
	 * @var Sort[] key is class name
	 */
	private $sort_options = [];

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
	public function add($class_name, $property_path)
	{
		if (is_array($property_path)) {
			$this->sort_options[$class_name] = new Sort($property_path);
		}
		else {
			$options = isset($this->sort_options[$class_name]) ? $this->sort_options[$class_name] : null;
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
	public function get($class_name, $property_path = null)
	{
		if (isset($property_path)) {
			return (
				isset($this->sort_options[$class_name])
				&& isset($this->sort_options[$class_name]->columns[$property_path])
			);
		}
		else {
			return isset($this->sort_options[$class_name])
				? $this->sort_options[$class_name]
				: new Sort($class_name);
		}
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
	public function remove($class_name, $property_path = null)
	{
		if (isset($property_path)) {
			$options = isset($this->sort_options[$class_name]) ? $this->sort_options[$class_name] : null;
			if (isset($options)) {
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
		}
		elseif (isset($this->sort_options[$class_name])) {
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
	public function reverse($class_name, $property_path)
	{
		$options = isset($this->sort_options[$class_name]) ? $this->sort_options[$class_name] : null;
		if (isset($options)) {
			if (in_array($property_path, $options->reverse)) {
				unset($options->reverse[array_search($property_path, $options->reverse)]);
			}
			else {
				array_push($options->reverse, $property_path);
			}
		}
	}

}
