<?php
namespace ITRocks\Framework\Traits;

/**
 * For any class that need a comment
 */
trait Has_Comment
{

	//-------------------------------------------------------------------------------------- $comment
	/**
	 * @max_length 50000
	 * @multiline
	 * @var string
	 */
	public string $comment;

}
