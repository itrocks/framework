<?php

class User
{

	/**
	 * @var User
	 */
	private static $current;

	/**
	 * @var string
	 */
	private $login;

	/**
	 * @var string
	 */
	private $password;

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

	//-------------------------------------------------------------------------------------- getLogin
	public function getLogin()
	{
		return $this->login;
	}

	//----------------------------------------------------------------------------------- getPassword
	public function getPassword()
	{
		return $this->password;
	}

	//------------------------------------------------------------------------------------ setCurrent
	public static function setCurrent($user)
	{
		User::$current = $user;
	}

	//-------------------------------------------------------------------------------------- setLogin
	public function setLogin($login)
	{
		$this->login = $login;
		return $this;
	}

	//----------------------------------------------------------------------------------- setPassword
	public function setPassword($password)
	{
		$this->password = $password;
		return $this;
	}

}
