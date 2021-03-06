<?php
namespace ITRocks\Framework\Traits;

/**
 * For all classes working on an object as representative value
 *
 * This object must embed a __toString() method for string representation
 *
 * @representative object
 */
trait Has_Object
{

	//--------------------------------------------------------------------------------------- $object
	/**
	 * @mandatory
	 * @var object
	 */
	public $object;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->object);
	}

}
