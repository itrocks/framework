<?php
namespace ITRocks\Framework\Trigger\Schedule;

use ITRocks\Framework\Reflection\Attribute\Class_\Override;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Reflection\Attribute\Property\User;
use ITRocks\Framework\Trigger;

/**
 * Action for schedule
 */
#[
	Override('keep_user',          new User(User::INVISIBLE)),
	Override('request_identifier', new User(User::INVISIBLE)),
	Override('status',             new User(User::INVISIBLE)),
	Store(false)
]
class Action extends Trigger\Action
{

}
