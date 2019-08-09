<?php
namespace ITRocks\Framework\Tools;

use Iterator;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;

/**
 * List data is an interface for all list data storage classes
 */
interface List_Data extends Iterator
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
	 * Gets the element's class
	 *
	 * @return Reflection_Class
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
	 * @return Reflection_Property[]
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

	//--------------------------------------------------------------------------------------- getRows
	/**
	 * @return List_Row[]
	 */
	public function getRows();

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

	//---------------------------------------------------------------------------------------- newRow
	/**
	 * Creates a new row
	 *
	 * @param $class_name string The class name of the main business object stored into the row
	 * @param $object     object The main business object stored into the row
	 * @param $row        array|object The data stored into the row
	 * @return List_Row
	 */
	public function newRow($class_name, $object, $row);

}
