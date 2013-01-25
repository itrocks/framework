<?php
namespace SAF\Framework;
use AopJoinpoint;

abstract class Acls_Loader
{

	//--------------------------------------------------------------------------------- loadGroupAcls
	/**
	 * @param $group Acl_Group
	 * @param $acls_rights Acls_Rights
	 * @return Acls_Rights
	 */
	public static function loadGroupAcls(Acl_Group $group, Acls_Rights $acls_rights = null)
	{
		if (!isset($acls_rights)) {
			$acls_rights = new Acls_Rights();
		}
		// TODO next line is a dangerous thing, see what it is needed for and if it can be removed
		Acls_User::current()->group = $group;
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
	 * @param $joinpoint AopJoinpoint
	 */
	public static function onUserAuthenticate(AopJoinpoint $joinpoint)
	{
		$arguments = $joinpoint->getArguments();
		if (isset($arguments)) {
			Session::current()->set(Acls_Rights::current(self::loadUserAcls($arguments[0])));
		}
	}

	//------------------------------------------------------------------------------ onUserDisconnect
	public static function onUserDisconnect()
	{
		Acls_Rights::current(new Acls_Rights());
		Session::current()->removeAny(__NAMESPACE__ . "\\Acls_Rights");
	}

}
