<?php
namespace ITRocks\Framework\Reflection\Annotation\Tests\Replaces;

use ITRocks\Framework\Reflection\Attribute\Property\Getter;

/**
 * Replacement property with getter test
 */
class Replacement_Getter
{

	//------------------------------------------------------------------------------------- $replaced
	public string $replaced;

	//---------------------------------------------------------------------------------- $replacement
	/**
	 * @replaces replaced
	 */
	#[Getter]
	public string $replacement;

	//-------------------------------------------------------------------------------- getReplacement
	protected function getReplacement() : string
	{
		return $this->replacement . '(get)';
	}

}
