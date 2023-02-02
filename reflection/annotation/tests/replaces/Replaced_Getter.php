<?php
namespace ITRocks\Framework\Reflection\Annotation\Tests\Replaces;

use ITRocks\Framework\Reflection\Attribute\Property\Getter;

/**
 * Replaced property with getter test
 */
class Replaced_Getter
{

	//------------------------------------------------------------------------------------- $replaced
	#[Getter]
	public string $replaced;

	//---------------------------------------------------------------------------------- $replacement
	/**
	 * @replaces replaced
	 */
	public string $replacement;

	//----------------------------------------------------------------------------------- getReplaced
	protected function getReplaced() : string
	{
		return $this->replaced . '(get)';
	}

}
