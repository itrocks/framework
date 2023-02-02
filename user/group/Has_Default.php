<?php
namespace ITRocks\Framework\User\Group;

use ITRocks\Framework\Reflection\Attribute\Class_\Extend;
use ITRocks\Framework\Traits;
use ITRocks\Framework\User\Group;

/**
 * @feature Select a default access group for registered users
 */
#[Extend(Group::class)]
trait Has_Default
{
	use Traits\Has_Default;

}
