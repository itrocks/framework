<?php
namespace ITRocks\Framework\Traits;

/**
 * For all classes having a name as representative value
 *
 * @representative name
 */
trait Has_Name
{

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @mandatory
	 * @var string
	 */
	public string $name = '';

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return $this->name;
	}

}
