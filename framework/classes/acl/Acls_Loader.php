<?php
namespace SAF\Framework;

abstract class Acls_Loader
{

	//--------------------------------------------------------------------------------- loadGroupAcls
	/**
	 * @param Acl_Group $group
	 * @return Acls
	 */
	public static function loadGroupAcls(Acl_Group $group)
	{
		
	}

	//---------------------------------------------------------------------------------- loadUserAcls
	/**
	 * @param User $user
	 * @return Acls
	 */
	public static function loadUserAcls(User $user)
	{
		$search = new User_Acl_Group();
		$search->user = $user;
		foreach (Dao::search($search) as $user_acl_group) {
			$this->loadUserAcl($user_acl_group->group);
		}
	}

}
