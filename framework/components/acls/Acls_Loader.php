<?php
namespace SAF\Framework;

/**
 * Acls loader tools load acls when needed
 */
abstract class Acls_Loader
{

	//--------------------------------------------------------------------------------- loadGroupAcls
	/**
	 * @param $group Acls_Group
	 * @param $acls_rights Acls_Rights
	 * @return Acls_Rights
	 */
	public static function loadGroupAcls(Acls_Group $group, Acls_Rights $acls_rights = null)
	{
		if (!isset($acls_rights)) {
			$acls_rights = new Acls_Rights();
		}
		foreach ($group->rights as $right) {
			$acls_rights->add($right);
		}
		return $acls_rights;
	}

	//---------------------------------------------------------------------------------- loadUserAcls
	/**
	 * @param $user Acls_User
	 * @param $acls Acls_Rights
	 * @return Acls_Rights
	 */
	public static function loadUserAcls(Acls_User $user, Acls_Rights $acls = null)
	{
		if (!isset($acls)) {
			$acls = new Acls_Rights();
		}
		return self::loadGroupAcls($user->group, $acls);
	}

	//---------------------------------------------------------------------------- onUserAuthenticate
	/**
	 * @param $user Acls_User
	 */
	public static function onUserAuthenticate(Acls_User $user)
	{
		Session::current()->set(Acls_Rights::current(self::loadUserAcls($user)));
	}

	//------------------------------------------------------------------------------ onUserDisconnect
	public static function onUserDisconnect()
	{
		Acls_Rights::current(new Acls_Rights);
		Session::current()->removeAny('SAF\Framework\Acls_Rights');
	}

}
