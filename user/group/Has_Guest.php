<?php
namespace ITRocks\Framework\User\Group;

use ITRocks\Framework\User\Group;

/**
 * @extends Group
 * @feature Select an access group for guest users
 * @see Group
 */
trait Has_Guest
{

	//---------------------------------------------------------------------------------------- $guest
	/**
	 * @var boolean
	 */
	public $guest = false;

}
