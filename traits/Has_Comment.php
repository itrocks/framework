<?php
namespace ITRocks\Framework\Traits;

use ITRocks\Framework\Reflection\Attribute\Property\Multiline;

/**
 * For any class that need a comment
 */
trait Has_Comment
{

	//-------------------------------------------------------------------------------------- $comment
	/** @max_length 50000 */
	#[Multiline]
	public string $comment;

}
