<?php
namespace ITRocks\Framework\Traits;

use ITRocks\Framework\Feature\Validate\Property\Max_Length;
use ITRocks\Framework\Reflection\Attribute\Property\Multiline;

/**
 * For any class that need a comment
 */
trait Has_Comment
{

	//-------------------------------------------------------------------------------------- $comment
	#[Max_Length(50000), Multiline]
	public string $comment;

}
