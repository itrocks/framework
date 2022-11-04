<?php
namespace ITRocks\Framework\Tools;

/**
 * List row is an interface for all list row storage classes (into a list data)
 */
interface List_Row
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name string
	 * @param $object     mixed Object or object identifier
	 * @param $values     string[]
	 * @param $list       List_Data
	 */
	public function __construct(string $class_name, mixed $object, array $values, List_Data $list);

	//----------------------------------------------------------------------------------------- count
	/**
	 * @return integer
	 */
	public function count() : int;

	//---------------------------------------------------------------------------------- getClassName
	/**
	 * Returns list row element class name
	 *
	 * @return string
	 */
	public function getClassName() : string;

	//------------------------------------------------------------------------------------- getObject
	/**
	 * Returns object associated to the list row
	 *
	 * @return object
	 */
	public function getObject() : object;

	//-------------------------------------------------------------------------------------- getValue
	/**
	 * Gets a value from the row
	 *
	 * @param $property string the path of the property
	 * @return mixed
	 */
	public function getValue(string $property) : mixed;

	//------------------------------------------------------------------------------------- getValues
	/**
	 * Gets all values from the row
	 *
	 * @return array
	 */
	public function getValues() : array;

	//-------------------------------------------------------------------------------------------- id
	/**
	 * Returns the row's DAO identifier
	 *
	 * @return mixed
	 */
	public function id() : mixed;

	//-------------------------------------------------------------------------------------- setValue
	/**
	 * @param $property string the path of the property
	 * @param $value    mixed the new value
	 */
	public function setValue(string $property, mixed $value);

}
