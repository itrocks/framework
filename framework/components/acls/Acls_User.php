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

	//--------------------------------------------------------------------------------------- current
	/**
	 * @see \SAF\Framework\User::current($set_current)
	 * @return Acls_User
	 */
	public static function current(Acls_User $user = null)
	{
		return parent::current($user);
	}

	//-------------------------------------------------------------------------------------- getGroup
	public function getGroup()
	{
		$this->group = Getter::getObject($this->group, __NAMESPACE__ . "\\Acl_Group", $this, "group");
		if (empty($this->group)) {
			$this->group = new Acl_Group();
			$this->group->caption = $this->login;
			$this->group->type = "user";
			Dao::write($this);
		}
		return $this->group;
	}

}
