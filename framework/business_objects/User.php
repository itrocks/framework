<?php
namespace SAF\Framework;

class User
{

	//-------------------------------------------------------------------------------------- $current
	/**
	 * @var User
	 */
	private static $current;

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

	//------------------------------------------------------------------------------------ getCurrent
	/**
	 * Get current user from current environment
	 * 
	 * @return \SAF\Framework\User
	 */
	public static function getCurrent()
	{
		return User::$current;
	}

	//------------------------------------------------------------------------------------ setCurrent
	/**
	 * Set current environment's user
	 *
	 * @param User $user
	 */
	public static function setCurrent($user)
	{
		User::$current = $user;
	}

}
