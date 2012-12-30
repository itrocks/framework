<?php
namespace SAF\Framework;

class Default_List_Row implements List_Row
{

	//--------------------------------------------------------------------------------------- $object
	/**
	 * @var object
	 */
	private $object;

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string
	 */
	public $class_name;

	//--------------------------------------------------------------------------------------- $values
	/**
	 * @var multitype:string
	 */
	public $values;

	//----------------------------------------------------------------------------------- __construct
	public function __construct($class_name, $object, $values)
	{
		$this->class_name = $class_name;
		$this->object = $object;
		$this->values = $values;
	}

	//----------------------------------------------------------------------------------------- count
	public function count()
	{
		return count($this->values);
	}

	//---------------------------------------------------------------------------------- getClassName
	public function getClassName()
	{
		return $this->class_name;
	}

	//------------------------------------------------------------------------------------- getObject
	public function getObject()
	{
		return Getter::getObject($this->object, $this->class_name);
	}

	//-------------------------------------------------------------------------------------- getValue
	public function getValue($property)
	{
		return $this->values[$property];
	}

	//-------------------------------------------------------------------------------------------- id
	public function id()
	{
		return $this->object;
	}

}
