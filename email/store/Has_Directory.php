<?php
namespace ITRocks\Framework\Email\Store;

use ITRocks\Framework\Email;
use ITRocks\Framework\Reflection\Attribute\Class_\Display_Order;
use ITRocks\Framework\Reflection\Attribute\Class_\Extend;

/**
 * @feature Store your emails into a directory
 */
#[Display_Order('account', 'directory'), Extend(Email::class)]
trait Has_Directory
{

	//------------------------------------------------------------------------------------ $directory
	public ?Directory $directory = null;

}
