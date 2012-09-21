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
		if (isset($acls)) {
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
	 * @param User $user
	 * @return Acls
	 */
	public static function loadUserAcls(User $user, Acls $acls = null)
	{
		if (isset($acls)) {
			$acls = new Acls();
		}
		$search = new Acl_Group_User();
		$search->user = $user;
		foreach (Dao::search($search) as $acl_group_user) {
			$this->loadGroupAcl($acl_group_user->group, $acls);
		}
		return $acls;
	}

}
