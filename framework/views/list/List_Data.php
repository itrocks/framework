<?php
namespace SAF\Framework;

interface List_Data
{

	//------------------------------------------------------------------------------------------- add
	/**
	 * Adds a row to the list
	 *
	 * @param List_Row $row
	 */
	public function add(List_Row $row);

	//------------------------------------------------------------------------------------- getObject
	/**
	 * Gets object associated to a row index
	 *
	 * @param integer $row_index 0..n
	 * @return object
	 */
	public function getObject($row_index);

	//---------------------------------------------------------------------------------------- getRow
	/**
	 * Gets row associated to a row index
	 *
	 * @param integer $row_index
	 * @return List_Row
	 */
	public function getRow($row_index);

	//-------------------------------------------------------------------------------------- getValue
	/**
	 * Gets displayable value from a list data cell
	 *
	 * @param integer $row_index
	 * @param string $property
	 * @return string
	 */
	public function getValue($row_index, $property);

}
