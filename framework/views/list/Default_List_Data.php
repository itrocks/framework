<?php
namespace SAF\Framework;

class Default_List_Data extends Set implements List_Data
{

	//------------------------------------------------------------------------------------------- add
	public function add(List_Row $row)
	{
		parent::add($row);
	}

	//------------------------------------------------------------------------------------- getObject
	public function getObject($row_index)
	{
		return $this->getRow($row_index)->getObject();
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
