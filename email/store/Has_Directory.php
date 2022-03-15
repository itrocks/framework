<?php
namespace ITRocks\Framework\Email\Store;

use ITRocks\Framework\Email;

/**
 * @display_order account, directory
 * @extends Email
 * @feature Store your emails into a directory
 */
trait Has_Directory
{

	//------------------------------------------------------------------------------------ $directory
	/**
	 * @link Object
	 * @var Directory|null
	 */
	public $directory = null;

}
