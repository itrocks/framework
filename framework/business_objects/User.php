<?php
namespace SAF\Framework;

class User
{

	/**
	 * @var User
	 */
	private static $current;

	/**
	 * @var string
	 */
	public $login;

	/**
	 * @var string
	 */
	public $password;

	//----------------------------------------------------------------------------------- __construct
	public function __construct($login = "", $password = "")
	{
		$this->login    = $login;
		$this->password = $password;
	}

	//------------------------------------------------------------------------------------ getCurrent
	public static function getCurrent()
	{
		return User::$current;
	}

	//------------------------------------------------------------------------------------ setCurrent
	public static function setCurrent($user)
	{
		User::$current = $user;
	}

}
