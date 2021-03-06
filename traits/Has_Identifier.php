<?php
namespace ITRocks\Framework\Traits;

/**
 * For anything that has an identifier
 *
 * @representative identifier
 */
trait Has_Identifier
{

	//----------------------------------------------------------------------------------- $identifier
	/**
	 * An internal identifier that helps users to found an address (eg name + city)
	 *
	 * @unique
	 * @var string
	 */
	public $identifier;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->identifier);
	}

}
