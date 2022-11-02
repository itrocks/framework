<?php
namespace ITRocks\Framework\Feature\List_;

/**
 * Search values for list component
 */
class Search_Values
{

	//-------------------------------------------------------------------------------- $search_values
	/**
	 * @var array[] Key is the class name, value is an array 'property_name' => 'value'
	 */
	private array $search_values;

	//------------------------------------------------------------------------------------------- get
	/**
	 * Gets the class name's search values, or a property search value
	 *
	 * @param $class_name    string the class name
	 * @param $property_name string if set, gets the value of one of the search properties
	 * @return string[]|string the search values for the class name, or the value for the property
	 */
	public function get(string $class_name, string $property_name = '') : array|string
	{
		if ($property_name === '') {
			return $this->search_values[$class_name] ?? [];
		}
		return $this->search_values[$class_name][$property_name] ?? '';
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * Remove a class name's search values, or a property search value
	 *
	 * @param $class_name    string
	 * @param $property_name string
	 */
	public function remove(string $class_name, string $property_name = '')
	{
		if ($property_name === '') {
			if (isset($this->search_values[$class_name])) {
				unset($this->search_values[$class_name]);
			}
			return;
		}
		if ($this->search_values[$class_name][$property_name] ?? false) {
			unset($this->search_values[$class_name][$property_name]);
			if (!$this->search_values[$class_name]) {
				unset($this->search_values[$class_name]);
			}
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
	public function set(string $class_name, string $property_name, string $value)
	{
		$this->search_values[$class_name][$property_name] = $value;
	}

}
