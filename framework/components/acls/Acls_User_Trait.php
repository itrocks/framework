<?php
namespace SAF\Framework;

trait Acls_User_Trait
{
	use Current { current as private pCurrent; }

	//---------------------------------------------------------------------------------------- $group
	/**
	 * @getter getGroup
	 * @link Object
	 * @var Acls_Group
	 */
	public $group;

	//--------------------------------------------------------------------------------------- current
	/**
	 * @param $set_current Acls_User
	 * @return Acls_User
	 * @see User::current()
	 */
	public static function current($set_current = null)
	{
		return self::pcurrent($set_current);
	}

	//-------------------------------------------------------------------------------------- getGroup
	/**
	 * @return Acls_Group
	 */
	public function getGroup()
	{
		$group = isset($this->group) ? $this->group : null;
		if (!isset($group)) {
			$group = Getter::getObject($group, 'SAF\Framework\Acls_Group', $this, "group");
			if (empty($group)) {
				/** @var $group Acls_Group */
				$group = Builder::create("Acls_Group");
				$group->name = ($this instanceof User) ? $this->login : "";
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
