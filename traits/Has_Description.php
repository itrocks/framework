<?php
namespace ITRocks\Framework\Traits;

use ITRocks\Framework\Feature\Validate\Property\Max_Length;
use ITRocks\Framework\Reflection\Attribute\Property\Multiline;

/**
 * For anything that needs a multiple-lines description
 */
trait Has_Description
{

	//---------------------------------------------------------------------------------- $description
	#[Max_Length(10000000), Multiline]
	public string $description = '';

}
