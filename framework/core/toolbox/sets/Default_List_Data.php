<?php
namespace SAF\Framework;

class Default_List_Data extends Set implements List_Data
{

	//----------------------------------------------------------------------------------- $properties
	/**
	 * @var string[]
	 */
	private $properties;

	//----------------------------------------------------------------------------------- __construct
	public function __construct($element_class_name, $properties)
	{
		parent::__construct($element_class_name);
		$this->properties = $properties;
	}

	//------------------------------------------------------------------------------------------- add
	public function add(List_Row $row)
	{
		parent::add($row);
	}

	//----------------------------------------------------------------------------------------- count
	public function count()
	{
		return count($this->properties);
	}

	//-------------------------------------------------------------------------------------- getClass
	public function getClass()
	{
		return $this->elementClass();
	}

	//------------------------------------------------------------------------------------- getObject
	public function getObject($row_index)
	{
		return $this->getRow($row_index)->getObject();
	}

	//--------------------------------------------------------------------------------- getProperties
	public function getProperties()
	{
		return $this->properties;
	}

	//---------------------------------------------------------------------------------------- getRow
	public function getRow($row_index)
	{
		return $this->get($row_index);
	}

	//-------------------------------------------------------------------------------------- getValue
	public function getValue($row_index, $property)
	{
		return $this->getRow($row_index)->getValue($property);
	}

}
