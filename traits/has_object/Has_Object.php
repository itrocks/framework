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
	public object $object;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return strval($this->object);
	}

}
