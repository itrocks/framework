<?php
namespace SAF\Framework;

/**
 * Use this trait for users needing acls
 */
trait Acls_User_Trait
{

	//---------------------------------------------------------------------------------------- $group
	/**
	 * @getter getGroup
	 * @link Object
	 * @var Acls_Group
	 */
	public $group;

	//-------------------------------------------------------------------------------------- getGroup
	/**
	 * @return Acls_Group
	 */
	/* @noinspection PhpUnusedPrivateMethodInspection @getter */
	private function getGroup()
	{
		$group = isset($this->group) ? $this->group : null;
		if (!isset($group)) {
			Getter::getObject($group, Acls_Group::class, $this, "group");
			if (empty($group)) {
				/** @var $group Acls_Group */
				$group = new Acls_Group();
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
