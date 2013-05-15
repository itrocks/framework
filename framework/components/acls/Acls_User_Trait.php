<?php
namespace SAF\Framework;

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
	public function getGroup()
	{
		$group = isset($this->group) ? $this->group : null;
		if (!isset($group)) {
			$group = Getter::getObject($group, 'SAF\Framework\Acls_Group', $this, "group");
			if (empty($group)) {
				/** @var $group Acls_Group */
				$group = Builder::create('SAF\Framework\Acls_Group');
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
