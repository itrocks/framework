<?php
namespace ITRocks\Framework\Traits;

use ITRocks\Framework\Reflection\Attribute\Property\Multiline;

/**
 * For anything that needs a multiple-lines description
 */
trait Has_Description
{

	//---------------------------------------------------------------------------------- $description
	/** @max_length 10000000 */
	#[Multiline]
	public string $description = '';

}
