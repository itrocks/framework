<?php
namespace ITRocks\Framework\Feature\Import\Settings;

use ITRocks\Framework\Feature\Import\Import_Array;

/**
 * Import preview data
 */
class Import_Preview
{

	//----------------------------------------------------------------------------------------- $data
	/**
	 * @var array two dimensional array (keys are row, col) with written data as value
	 */
	public array $data;

	//------------------------------------------------------------------------------------ $first_row
	/**
	 * First visible row (to avoid display of class name, constants and properties rows)
	 *
	 * @var integer
	 */
	public int $first_row;

	//---------------------------------------------------------------------------------- $last_column
	/**
	 * Last visible column (to avoid display of constants columns)
	 *
	 * @var integer
	 */
	public int $last_column;

	//------------------------------------------------------------------------------------- $last_row
	/**
	 * Last visible row (remember we display a preview, not the entire file)
	 *
	 * @var integer
	 */
	public int $last_row;

	//----------------------------------------------------------------------------------- $properties
	/**
	 * @var string[] property names
	 */
	public array $properties;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $data       array|null
	 * @param $properties string[]|null
	 */
	public function __construct(array $data = null, array $properties = null)
	{
		if (isset($properties)) {
			$this->properties = $properties;
		}
		if (isset($data)) {
			$this->data = $data;
			$constants  = Import_Array::getConstantsFromArray($data);
			$row        = current($data);
			if (!isset($this->properties)) {
				foreach ($row as $column_number => $property_path) {
					if (!isset($constants[$property_path])) {
						$this->properties[$column_number] = $property_path;
					}
				}
				next($data);
			}
			// next row is the first row (in 1..n keys instead of 0..n of the $data array)
			$this->first_row   = key($data) + 1;
			$this->last_row    = min($this->first_row + 10, count($this->data));
			$this->last_column = count($this->properties);
		}
	}

}
