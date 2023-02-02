<?php
namespace ITRocks\Framework\User\Group;

use ITRocks\Framework\Reflection\Attribute\Class_\Extend;
use ITRocks\Framework\User\Group;

/**
 * @feature Select an access group for guest users
 */
#[Extend(Group::class)]
trait Has_Guest
{

	//---------------------------------------------------------------------------------------- $guest
	/**
	 * @var boolean
	 */
	public bool $guest = false;

}
