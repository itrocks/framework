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
	 * @var string
	 */
	public $password;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Build a User object, optionnaly with it's login and password initialization
	 *
	 * @param string $login
	 * @param string $password
	 */
	public function __construct($login = "", $password = "")
	{
		$this->login    = $login;
		$this->password = $password;
	}

	//--------------------------------------------------------------------------------------- current
	/**
	 * @param User $set_current
	 * @return User
	 */
	public static function current(User $set_current = null)
	{
		return self::pCurrent($set_current);
	}

}
