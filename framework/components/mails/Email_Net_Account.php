<?php
namespace SAF\Framework;

abstract class Email_Net_Account
{

	//----------------------------------------------------------------------------------------- $host
	/**
	 * @var string
	 */
	public $host;

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
	public function __construct($host = null, $login = null, $password = null)
	{
		if (isset($host))     $this->host     = $host;
		if (isset($login))    $this->login    = $login;
		if (isset($password)) $this->password = $password;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->host . ":" . $this->login;
	}

}
