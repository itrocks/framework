<?php
namespace ITRocks\Framework\Widget\Data_List;

/**
 * Search values for list component
 */
class Search_Values
{

	//-------------------------------------------------------------------------------- $search_values
	/**
	 * @var array[] Key is the class name, value is an array 'property_name' => 'value'
	 */
	private $search_values;

	//------------------------------------------------------------------------------------------- get
	/**
	 * Gets the class name's search values, or a property search value
	 *
	 * @param $class_name    string the class name
	 * @param $property_name string if set, gets the value of one of the search properties
	 * @return string[]|string the search values for the class name, or the value for the property
	 */
	public function get($class_name, $property_name = null)
	{
		if (isset($property_name)) {
			return (
				isset($this->search_values[$class_name])
				&& isset($this->search_values[$class_name][$property_name])
			) ? $this->search_values[$class_name][$property_name] : null;
		}
		else {
			return isset($this->search_values[$class_name]) ? $this->search_values[$class_name] : [];
		}
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * Remove a class name's search values, or a property search value
	 *
	 * @param $class_name    string
	 * @param $property_name string
	 */
	public function remove($class_name, $property_name = null)
	{
		if (isset($property_name)) {
			if (
				isset($this->search_values[$class_name])
				&& isset($this->search_values[$class_name][$property_name])
			) {
				unset($this->search_values[$class_name][$property_name]);
				if (!$this->search_values[$class_name]) {
					unset($this->search_values[$class_name]);
				}
			}
		}
		elseif (isset($this->search_values[$class_name])) {
			unset($this->search_values[$class_name]);
		}
	}

	//------------------------------------------------------------------------------------------- set
	/**
	 * Sets a property search value
	 *
	 * @param $class_name    string
	 * @param $property_name string
	 * @param $value         string
	 */
	public function set($class_name, $property_name, $value)
	{
		$this->search_values[$class_name][$property_name] = $value;
	}

}
