<?php
namespace SAF\Framework;

/**
 * A user business object for all your uses in user authentication
 *
 * @representative login
 */
class User
{
	use Account;
	use Current { current as private pCurrent; }

	//--------------------------------------------------------------------------------------- current
	/**
	 * @param $set_current User
	 * @return User
	 */
	public static function current($set_current = null)
	{
		return self::pCurrent($set_current);
	}

}
