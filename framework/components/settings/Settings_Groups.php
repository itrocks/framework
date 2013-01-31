<?php
namespace SAF\Framework;

class Settings_Groups
{

	//----------------------------------------------------------------------------------------- __get
	/**
	 * Gets all Acls_Group[] of a given type from Dao, as if there where a property of this class
	 *
	 * @param $type string any possible Acls_Group::$type value
	 * @return Acls_Group[]
	 */
	public function __get($type)
	{
		$group = new Acls_Group();
		$group->type = $type;
		return Dao::search($group);
	}

}
