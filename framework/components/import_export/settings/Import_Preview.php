<?php
namespace SAF\Framework;

/**
 * Import preview data
 */
class Import_Preview
{

	//----------------------------------------------------------------------------------------- $data
	/**
	 * @var array two dimensional array (keys are row, col) with written data as value
	 */
	public $data;

	//------------------------------------------------------------------------------------ $first_row
	/**
	 * First visible row (to avoid display of class name, constants and properties rows
	 *
	 * @var integer
	 */
	public $first_row;

	//---------------------------------------------------------------------------------- $last_column
	/**
	 * Last visible column (to avoid display of constants columns)
	 *
	 * @var integer
	 */
	public $last_column;

	//----------------------------------------------------------------------------------- $properties
	/**
	 * @var string[] property names
	 */
	public $properties;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $data       array
	 * @param $properties string[]
	 */
	public function __construct($data = null, $properties = null)
	{
		if (isset($properties)) {
			$this->properties = $properties;
		}
		if (isset($data)) {
			$this->data = $data;
			$row = reset($data);
			$constants = array();
			if ((count($row) < 2) || $row[1] == "") {
				$row = next($data);
			}
			while (current($data) && (count($row) == 3) && ($row[1] == "=")) {
				$constants[$row[0]] = $row[2];
				$row = next($data);
			}
			if (!isset($this->properties)) {
				foreach ($row as $column_number => $property_path) {
					if (!isset($constants[$property_path])) {
						$this->properties[$column_number] = $property_path;
					}
				}
				next($data);
			}
			// next row is the first row (in 1..n keys instead of 0..n of the $data array)
			$this->first_row = key($data) + 1;
			$this->last_column = count($this->properties);
		}
	}

}
