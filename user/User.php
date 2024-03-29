<?php
namespace ITRocks\Framework;

use ITRocks\Framework\Reflection\Attribute\Class_\Representative;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Tools\Current;
use ITRocks\Framework\User\Account;
use ITRocks\Framework\User\Group;
use ITRocks\Framework\User\Group\Has_Groups;
use ITRocks\Framework\User\Group\Has_Guest;

/**
 * A user business object for all your uses in user authentication
 *
 * @feature
 */
#[Representative('login'), Store]
class User
{
	use Account;
	use Current { current as private pCurrent; }

	//--------------------------------------------------------------------------------------- current
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @noinspection PhpDocSignatureInspection $set_current static
	 * @param $set_current static Only set when a user, but other parameters
	 *        set by #Default User::current or @user_default User::current can be set and ignored
	 * @return ?static
	 */
	public static function current(self $set_current = null) : ?static
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
	 */
	public function hasAccessTo(?string $uri) : bool
	{
		return true;
	}

}
