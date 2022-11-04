<?php
namespace ITRocks\Framework\User\Group;

use ITRocks\Framework\User\Group;

/**
 * @extends Group
 * @feature Select an access group for guest users
 */
trait Has_Guest
{

	//---------------------------------------------------------------------------------------- $guest
	/**
	 * @var boolean
	 */
	public bool $guest = false;

}
