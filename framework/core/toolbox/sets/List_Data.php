<?php
namespace SAF\Framework;

interface List_Data
{

	//------------------------------------------------------------------------------------------- add
	/**
	 * Adds a row to the list
	 *
	 * @param $row List_Row
	 */
	public function add($row);

	//----------------------------------------------------------------------------------------- count
	/**
	 * Gets the properties count for each element
	 *
	 * @return integer
	 */
	public function count();

	//-------------------------------------------------------------------------------------- getClass
	/**
	 * Gets the element's class name
	 *
	 * @return string
	 */
	public function getClass();

	//------------------------------------------------------------------------------------- getObject
	/**
	 * Gets object associated to a row index
	 *
	 * @param $row_index integer 0..n
	 * @return object
	 */
	public function getObject($row_index);

	//--------------------------------------------------------------------------------- getProperties
	/**
	 * Returns the properties path list
	 *
	 * @return string[]
	 */
	public function getProperties();

	//---------------------------------------------------------------------------------------- getRow
	/**
	 * Gets row associated to a row index
	 *
	 * @param $row_index integer
	 * @return List_Row
	 */
	public function getRow($row_index);

	//-------------------------------------------------------------------------------------- getValue
	/**
	 * Gets displayable value from a list data cell
	 *
	 * @param $row_index integer
	 * @param $property string
	 * @return string
	 */
	public function getValue($row_index, $property);

	//---------------------------------------------------------------------------------------- length
	/**
	 * Gets the number of elements into the list
	 *
	 * @return integer
	 */
	public function length();

}
