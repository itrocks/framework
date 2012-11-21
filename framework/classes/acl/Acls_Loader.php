<?php
namespace SAF\Framework;

abstract class Acls_Loader
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
		$search = new Acl_Right();
		$search->group = $group;
		foreach (Dao::search($search) as $right) {
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
		$this->loadGroupAcl($user->group, $acls);
		return $acls;
	}

}
