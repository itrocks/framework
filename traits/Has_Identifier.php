<?php
namespace ITRocks\Framework\Traits;

use ITRocks\Framework\Reflection\Attribute\Class_\Representative;

/**
 * For anything that has an identifier
 */
#[Representative('identifier')]
trait Has_Identifier
{

	//----------------------------------------------------------------------------------- $identifier
	/**
	 * An internal identifier that helps users to found an address (eg name + city)
	 *
	 * @unique
	 */
	public string $identifier = '';

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return $this->identifier;
	}

}
