<?php
namespace SAF\Framework\Tools;

/**
 * A default list data set class : this stores data (visible strings and linked object) for list views
 */
class Default_List_Data extends Set implements List_Data
{

	//----------------------------------------------------------------------------------- $properties
	/**
	 * @var string[]
	 */
	private $properties;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $element_class_name string the reference class name
	 * @param $properties         string[] properties names
	 */
	public function __construct($element_class_name, $properties)
	{
		parent::__construct($element_class_name);
		$this->properties = $properties;
	}

	//------------------------------------------------------------------------------------------- add
	/**
	 * @param $row     List_Row a row element
	 * @param $element null The element should always be null, we only need the row
	 */
	public function add($row, $element = null)
	{
		parent::add($row, null);
	}

	//----------------------------------------------------------------------------------------- count
	/**
	 * @return integer
	 */
	public function count()
	{
		return count($this->properties);
	}

	//-------------------------------------------------------------------------------------- getClass
	/**
	 * @return Reflection_Class
	 */
	public function getClass()
	{
		return $this->elementClass();
	}

	//------------------------------------------------------------------------------------- getObject
	/**
	 * @param $row_index integer
	 * @return object
	 */
	public function getObject($row_index)
	{
		return $this->getRow($row_index)->getObject();
	}

	//--------------------------------------------------------------------------------- getProperties
	/**
	 * Gets properties names list
	 *
	 * @return string[]
	 */
	public function getProperties()
	{
		return $this->properties;
	}

	//---------------------------------------------------------------------------------------- getRow
	/**
	 * @param $row_index integer
	 * @return List_Row
	 */
	public function getRow($row_index)
	{
		return $this->get($row_index);
	}

	//-------------------------------------------------------------------------------------- getValue
	/**
	 * @param $row_index integer
	 * @param $property  string
	 * @return mixed
	 */
	public function getValue($row_index, $property)
	{
		return $this->getRow($row_index)->getValue($property);
	}

}
