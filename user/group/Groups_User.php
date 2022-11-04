<?php
namespace ITRocks\Framework\User\Group;

use ITRocks\Framework\User;

/**
 * This user should never have been used into your code : it is needed by Admin_Plugin only, nothing
 * else
 *
 * @business false
 * @feature false
 * @override groups @foreign user
 * @private
 * @store_name users
 */
class Groups_User extends User
{
	use Has_Groups;

}
