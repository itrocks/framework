<?php
namespace SAF\Framework;

class Acls_User extends User
{

	//---------------------------------------------------------------------------------------- $group
	/**
	 * @getter getGroup
	 * @var Acls_Group
	 */
	public $group;

	//--------------------------------------------------------------------------------------- current
	/**
	 * @see \SAF\Framework\User::current($set_current)
	 * @param $user Acls_User
	 * @return Acls_User
	 */
	public static function current($user = null)
	{
		return parent::current($user);
	}

	//-------------------------------------------------------------------------------------- getGroup
	/**
	 * @return Acls_Group
	 */
	public function getGroup()
	{
		$group = $this->group;
		if (!isset($group)) {
			$group = Getter::getObject($group, __NAMESPACE__ . "\\Acls_Group", $this);
			if (empty($group)) {
				$group = new Acls_Group();
				$group->name = $this->login;
				$group->type = "user";
				$this->group = $group;
				Dao::write($this);
			}
			else {
				$this->group = $group;
			}
		}
		return $group;
	}

}
