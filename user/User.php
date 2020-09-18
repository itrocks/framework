<?php
namespace ITRocks\Framework;

use ITRocks\Framework\Tools\Current;
use ITRocks\Framework\User\Account;
use ITRocks\Framework\User\Group;
use ITRocks\Framework\User\Group\Has_Groups;
use ITRocks\Framework\User\Group\Has_Guest;

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
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $set_current User|object Only set when an user, but other parameters
	 *        set by @default User::current or @user_default User::current can be set and ignored
	 * @return static
	 */
	public static function current($set_current = null)
	{
		$user = self::pCurrent(($set_current instanceof User) ? $set_current : null);
		if (
			!$user
			&& isA(Builder::className(User::class), Has_Groups::class)
			&& isA(Builder::className(Group::class), Has_Guest::class)
		) {
			/** @noinspection PhpUnhandledExceptionInspection constant class */
			$user         = Builder::create(User::class);
			$user->groups = Dao::search(['guest' => true], Group::class);
			User::current($user);
		}
		return $user;
	}

	//----------------------------------------------------------------------------------- hasAccessTo
	/**
	 * When no access control plugin is installed : any user has access to anything.
	 * Access control plugins override this to implement access control.
	 *
	 * @param $uri string
	 * @return boolean
	 */
	public function hasAccessTo(/* @noinspection PhpUnusedParameterInspection */ $uri)
	{
		return true;
	}

}
