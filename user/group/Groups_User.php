<?php
namespace ITRocks\Framework\User\Group;

use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\User;

/**
 * This user should never have been used into your code : it is needed by Admin_Plugin only, nothing
 * else
 *
 * @feature false
 * @override groups @foreign user
 * @private
 */
#[Store(false)]
class Groups_User extends User
{
	use Has_Groups;

}
