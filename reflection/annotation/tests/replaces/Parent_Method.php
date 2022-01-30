<?php
namespace ITRocks\Framework\Reflection\Annotation\Tests\Replaces;

/**
 * Parent method @replaces test
 */
class Parent_Method
{

	//------------------------------------------------------------------------------ $replaced_object
	/**
	 * @getter getReplacedObject
	 * @var Parent_Class
	 */
	public $replaced_object;

	//------------------------------------------------------------------------------ $replaced_string
	/**
	 * @getter getReplacedString
	 * @var string
	 */
	public $replaced_string;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * constructor
	 */
	public function __construct()
	{
		$this->replaced_object = new Parent_Class();
		$this->replaced_object->replaced = 'to_replaced_object';
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return $this->replaced_string . DOT . $this->replaced_object->replaced;
	}

	//----------------------------------------------------------------------------- getReplacedObject
	/**
	 * @return Parent_Class
	 */
	public function getReplacedObject() : Parent_Class
	{
		return $this->replaced_object;
	}

	//----------------------------------------------------------------------------- getReplacedString
	/**
	 * @return string
	 */
	public function getReplacedString() : string
	{
		return $this->replaced_string . '.get';
	}

}
