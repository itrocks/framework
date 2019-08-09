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
	 * @param $object     object
	 * @param $values     string[]
	 * @param $list       List_Data
	 */
	public function __construct($class_name, $object, array $values, List_Data $list);

	//----------------------------------------------------------------------------------------- count
	/**
	 * @return integer
	 */
	public function count();

	//---------------------------------------------------------------------------------- getClassName
	/**
	 * Returns list row element class name
	 *
	 * @return string
	 */
	public function getClassName();

	//------------------------------------------------------------------------------------- getObject
	/**
	 * Returns object associated to the list row
	 *
	 * @return object
	 */
	public function getObject();

	//-------------------------------------------------------------------------------------- getValue
	/**
	 * Gets a value from the row
	 *
	 * @param $property string the path of the property
	 * @return mixed
	 */
	public function getValue($property);

	//------------------------------------------------------------------------------------- getValues
	/**
	 * Gets all values from the row
	 *
	 * @return array
	 */
	public function getValues();

	//-------------------------------------------------------------------------------------------- id
	/**
	 * Returns the row's DAO identifier
	 *
	 * @return mixed
	 */
	public function id();

	//-------------------------------------------------------------------------------------- setValue
	/**
	 * @param $property string the path of the property
	 * @param $value    mixed the new value
	 */
	public function setValue($property, $value);

}
