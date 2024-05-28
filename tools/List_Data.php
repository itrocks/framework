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
	public function add(List_Row $row) : void;

	//----------------------------------------------------------------------------------------- count
	/**
	 * Gets the properties count for each element
	 *
	 * @return integer
	 */
	public function count() : int;

	//------------------------------------------------------------------------------------ firstValue
	/**
	 * @return mixed
	 */
	public function firstValue() : mixed;

	//-------------------------------------------------------------------------------------- getClass
	/**
	 * Gets the element's class
	 *
	 * @return Reflection_Class
	 */
	public function getClass() : Reflection_Class;

	//------------------------------------------------------------------------------------- getObject
	/**
	 * Gets object associated to a row index
	 *
	 * @param $row_index integer 0..n
	 * @return ?object
	 */
	public function getObject(int $row_index) : ?object;

	//--------------------------------------------------------------------------------- getProperties
	/**
	 * Returns the properties path list
	 *
	 * @return Reflection_Property[]
	 */
	public function getProperties() : array;

	//---------------------------------------------------------------------------------------- getRow
	/**
	 * Gets row associated to a row index
	 *
	 * @param $row_index integer
	 * @return List_Row
	 */
	public function getRow(int $row_index) : List_Row;

	//--------------------------------------------------------------------------------------- getRows
	/**
	 * @return List_Row[]
	 */
	public function getRows() : array;

	//-------------------------------------------------------------------------------------- getValue
	/**
	 * Gets displayable value from a list data cell
	 *
	 * @param $row_index integer
	 * @param $property string
	 * @return mixed
	 */
	public function getValue(int $row_index, string $property) : mixed;

	//---------------------------------------------------------------------------------------- length
	/**
	 * Gets the number of elements into the list
	 *
	 * @return integer
	 */
	public function length() : int;

	//---------------------------------------------------------------------------------------- newRow
	/**
	 * Creates a new row
	 *
	 * @param $class_name string The class name of the main business object stored into the row
	 * @param $object     object The main business object stored into the row
	 * @param $values     array  The values to store into the row
	 * @return List_Row
	 */
	public function newRow(string $class_name, object $object, array $values) : List_Row;

	//-------------------------------------------------------------------------------- removeProperty
	/**
	 * @param $property string
	 */
	public function removeProperty(string $property) : void;

}
