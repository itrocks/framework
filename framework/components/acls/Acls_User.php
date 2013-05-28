<?php
namespace SAF\Framework;

/**
 * An user with acls management
 */
class Acls_User extends User
{
	use Acls_User_Trait;

	//--------------------------------------------------------------------------------------- current
	/**
	 * @param $set_current Acls_User
	 * @return Acls_User
	 */
	public static function current($set_current = null)
	{
		return parent::current($set_current);
	}

}
