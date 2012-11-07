<?php
namespace SAF\Framework;

class Default_List_Row implements List_Row
{

	//--------------------------------------------------------------------------------------- $object
	/**
	 * @var object
	 */
	private $object;

	//--------------------------------------------------------------------------------- $object_class
	/**
	 * @var string
	 */
	public $object_class;

	//--------------------------------------------------------------------------------------- $values
	/**
	 * @var multitype:string
	 */
	public $values;

	//----------------------------------------------------------------------------------- __construct
	public function __construct($object_class, $object, $values)
	{
		$this->object_class = $object_class;
		$this->object = $object;
		$this->values = $values;
	}

	//------------------------------------------------------------------------------------- getObject
	public function getObject()
	{
		return Getter::getObject($this->object, $this->object_class);
	}

	//-------------------------------------------------------------------------------------- getValue
	public function getValue($property)
	{
		return $this->values[$property];
	}

	//------------------------------------------------------------------------------------------ size
	public function count()
	{
		return count($this->values);
	}

	//-------------------------------------------------------------------------------------------- id
	public function id()
	{
		return 1;
	}

}
