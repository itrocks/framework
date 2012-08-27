<?php

class User
{

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
