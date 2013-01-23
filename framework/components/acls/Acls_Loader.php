<?php
namespace SAF\Framework;
use AopJoinpoint;

abstract class Acls_Loader
{

	//--------------------------------------------------------------------------------- loadGroupAcls
	/**
	 * @param Acl_Group $group
	 * @param Acls_Rights $acls_rights
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
	 * @param Acls_User $user
	 * @param Acls $acls
	 * @return Acls
	 */
	public static function loadUserAcls(Acls_User $user, Acls $acls = null)
	{
		if (!isset($acls)) {
			$acls = new Acls_Rights();
		}
		return self::loadGroupAcls($user->group, $acls);
	}

	//---------------------------------------------------------------------------- onUserAuthenticate
	/**
	 * @param AopJoinpoint $joinpoint
	 */
	public static function onUserAuthenticate(AopJoinpoint $joinpoint)
	{
		$arguments = $joinpoint->getArguments();
		if (isset($arguments)) {
			Session::current()->set(Acls_Rights::current(self::loadUserAcls($arguments[0])));
		}
	}

	//------------------------------------------------------------------------------ onUserDisconnect
	/**
	 * @param AopJoinpoint $joinpoint
	 */
	public static function onUserDisconnect(AopJoinpoint $joinpoint)
	{
		Acls_Rights::current(new Acls_Rights());
		Session::current()->removeAny(__NAMESPACE__ . "\\Acls");
	}

}
