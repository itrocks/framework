<?php
namespace ITRocks\Framework\Traits;

use ITRocks\Framework\Reflection\Attribute\Class_\Unique;

/**
 * For all classes having a code
 */
#[Unique('code')]
trait Has_Code
{

	//----------------------------------------------------------------------------------------- $code
	public string $code = '';

}
