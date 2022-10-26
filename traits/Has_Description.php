<?php
namespace ITRocks\Framework\Traits;

/**
 * For anything that needs a multiple-lines description
 */
trait Has_Description
{

	//---------------------------------------------------------------------------------- $description
	/**
	 * @max_length 10000000
	 * @multiline
	 * @var string
	 */
	public string $description = '';

}
