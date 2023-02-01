<?php
namespace ITRocks\Framework\Email\Store;

use ITRocks\Framework\Email;
use ITRocks\Framework\Reflection\Attribute\Class_\Extends_;

/**
 * @display_order account, directory
 * @feature Store your emails into a directory
 */
#[Extends_(Email::class)]
trait Has_Directory
{

	//------------------------------------------------------------------------------------ $directory
	/**
	 * @link Object
	 * @var ?Directory
	 */
	public ?Directory $directory = null;

}
