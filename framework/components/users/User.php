<?php
namespace SAF\Framework;

class User
{
	use Current { current as private pCurrent; }

	//---------------------------------------------------------------------------------------- $login
	/**
	 * @var string
	 */
	public $login;

	//------------------------------------------------------------------------------------- $password
	/**
	 * @password sha1
	 * @var string
	 */
	public $password;

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
