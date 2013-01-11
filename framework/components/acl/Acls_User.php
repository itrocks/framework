<?php
namespace SAF\Framework;

class Acls_User extends User
{

	//---------------------------------------------------------------------------------------- $group
	/**
	 * @getter getGroup
	 * @var Acl_Group
	 */
	public $group;

	//-------------------------------------------------------------------------------------- getGroup
	public function getGroup()
	{
		$this->group = Getter::getObject($this->group, __NAMESPACE__ . "\\Acl_Group");
		if (empty($this->group)) {
			$this->group = new Acl_Group();
			$this->group->caption = "user:" . $this->login;
			Dao::write($this);
		}
		return $this->group;
	}

}
