<?php
namespace ITRocks\Framework\User\Group;

use ITRocks\Framework\Reflection\Attribute\Class_\Extends_;
use ITRocks\Framework\User\Group;

/**
 * @feature Select an access group for guest users
 */
#[Extends_(Group::class)]
trait Has_Guest
{

	//---------------------------------------------------------------------------------------- $guest
	/**
	 * @var boolean
	 */
	public bool $guest = false;

}
