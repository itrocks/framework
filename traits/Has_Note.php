<?php
namespace ITRocks\Framework\Traits;

use ITRocks\Framework\Reflection\Attribute\Property\Multiline;

/**
 * For any class that need a note
 */
trait Has_Note
{

	//----------------------------------------------------------------------------------------- $note
	/** @max_length 50000 */
	#[Multiline]
	public string $note = '';

}
