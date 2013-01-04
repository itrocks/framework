<?php
namespace SAF\Framework;
use AopJoinpoint;

abstract class Acls_Loader implements Plugin
{

	//--------------------------------------------------------------------------------- loadGroupAcls
	/**
	 * @param Acl_Group $group
	 * @return Acls
	 */
	public static function loadGroupAcls(Acl_Group $group, Acls $acls = null)
	{
		if (!isset($acls)) {
			$acls = new Acls();
		}
		// TODO next line is a dangerous thing, see what it is needed for and if it can be removed
		Acls_User::current()->group = $group;
		foreach ($group->rights as $right) {
			$acls->add($right);
		}
		return $acls;
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
			$acls = new Acls();
		}
		if ($user->group) {
			self::loadGroupAcls($user->group, $acls);
		}
		return $acls;
	}

	//---------------------------------------------------------------------------- onUserAuthenticate
	/**
	 * @param AopJoinpoint $joinpoint
	 */
	public static function onUserAuthenticate(AopJoinpoint $joinpoint)
	{
		$arguments = $joinpoint->getArguments();
		if (isset($arguments)) {
			Session::current()->set(Acls::current(self::loadUserAcls($arguments[0])));
		}
	}

	//------------------------------------------------------------------------------ onUserDisconnect
	/**
	 * @param AopJoinpoint $joinpoint
	 */
	public static function onUserDisconnect(AopJoinpoint $joinpoint)
	{
		Acls::current(new Acls());
		Session::current()->removeAny(__NAMESPACE__ . "\\Acls");
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::add("after",
			__NAMESPACE__ . "\\User_Authenticate_Controller->authenticate()",
			array(__CLASS__, "onUserAuthenticate")
		);
		Aop::add("after",
			__NAMESPACE__ . "\\User_Authenticate_Controller->disconnect()",
			array(__CLASS__, "onUserDisconnect")
		);
	}

}
