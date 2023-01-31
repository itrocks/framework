<?php
namespace ITRocks\Framework\Trigger\Schedule;

use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Trigger;

/**
 * Action for schedule
 *
 * @override keep_user @user invisible
 * @override request_identifier @user invisible
 * @override status @user invisible
 */
#[Store(false)]
class Action extends Trigger\Action
{

}
