<?php
namespace ITRocks\Framework\User\Group;

use ITRocks\Framework\Reflection\Attribute\Class_\Extends_;
use ITRocks\Framework\Traits;
use ITRocks\Framework\User\Group;

/**
 * @feature Select a default access group for registered users
 */
#[Extends_(Group::class)]
trait Has_Default
{
	use Traits\Has_Default;

}
