<?php
namespace SAF\Framework;

class Acls_User extends User
{

	//---------------------------------------------------------------------------------------- $group
	/**
	 * @getter Aop::getObject
	 * @var Acl_Group
	 */
	public $group;

	//---------------------------------------------------------------------------------- getUserGroup
	public function getUserGroup()
	{
		if (!$this->group) {
			$this->group = new Acl_Group();
			$this->group->caption = "user:" . $this->login;
			Dao::write($this);
		}
		return $this->group;
	}

}
