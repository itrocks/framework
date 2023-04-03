<?php
namespace ITRocks\Framework\Traits;

use ITRocks\Framework\Reflection\Attribute\Property\Mandatory;

/**
 * For all classes having a name as representative value
 *
 * @representative name
 */
trait Has_Name
{

	//----------------------------------------------------------------------------------------- $name
	#[Mandatory]
	public string $name = '';

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return $this->name;
	}

}
