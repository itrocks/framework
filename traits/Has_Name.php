<?php
namespace ITRocks\Framework\Traits;

/**
 * For all classes having a name as representative value
 *
 * @representative name
 * @sort name
 */
trait Has_Name
{

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @mandatory
	 * @var string
	 */
	public $name;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->name);
	}

}
