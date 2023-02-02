<?php
namespace ITRocks\Framework\Reflection\Annotation\Tests\Replaces;

use ITRocks\Framework\Reflection\Attribute\Property\Getter;

/**
 * Parent method @replaces test
 */
class Parent_Method
{

	//------------------------------------------------------------------------------ $replaced_object
	#[Getter('getReplacedObject')]
	public Parent_Class $replaced_object;

	//------------------------------------------------------------------------------ $replaced_string
	#[Getter('getReplacedString')]
	public string $replaced_string;

	//----------------------------------------------------------------------------------- __construct
	public function __construct()
	{
		$this->replaced_object = new Parent_Class();
		$this->replaced_object->replaced = 'to_replaced_object';
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return $this->replaced_string . DOT . $this->replaced_object->replaced;
	}

	//----------------------------------------------------------------------------- getReplacedObject
	public function getReplacedObject() : Parent_Class
	{
		return $this->replaced_object;
	}

	//----------------------------------------------------------------------------- getReplacedString
	public function getReplacedString() : string
	{
		return $this->replaced_string . '.get';
	}

}
