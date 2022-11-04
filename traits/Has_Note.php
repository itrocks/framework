<?php
namespace ITRocks\Framework\Traits;

/**
 * For any class that need a note
 */
trait Has_Note
{

	//----------------------------------------------------------------------------------------- $note
	/**
	 * @max_length 50000
	 * @multiline
	 * @var string
	 */
	public string $note = '';

}
