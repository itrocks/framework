<?php
namespace SAF\Framework;

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
	 */
	public function __construct($class_name, $object, $values);

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

	//-------------------------------------------------------------------------------------------- id
	/**
	 * Returns the row's DAO identifier
	 *
	 * @return mixed
	 */
	public function id();

}
