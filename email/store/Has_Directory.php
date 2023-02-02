<?php
namespace ITRocks\Framework\Email\Store;

use ITRocks\Framework\Email;
use ITRocks\Framework\Reflection\Attribute\Class_\Extend;

/**
 * @display_order account, directory
 * @feature Store your emails into a directory
 */
#[Extend(Email::class)]
trait Has_Directory
{

	//------------------------------------------------------------------------------------ $directory
	public ?Directory $directory = null;

}
