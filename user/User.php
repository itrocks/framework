<?php
namespace ITRocks\Framework;

use ITRocks\Framework\Tools\Current;
use ITRocks\Framework\User\Account;

/**
 * A user business object for all your uses in user authentication
 *
 * @business
 * @feature
 * @representative login
 */
class User
{
	use Account;
	use Current { current as private pCurrent; }

	//--------------------------------------------------------------------------------------- current
	/**
	 * @param $set_current User|object Only set when an user, but other parameters
	 *        set by @default User::current or @user_default User::current can be set and ignored
	 * @return User
	 */
	public static function current($set_current = null)
	{
		return self::pCurrent(($set_current instanceof User) ? $set_current : null);
	}

}
