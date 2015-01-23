<?php
namespace SAF\Framework\User;

/**
 * An account can connect to an application (or to anything that needs authentication)
 *
 * It has a login, a password, and an email for password recovery automation.
 *
 * @business
 */
trait Account
{

	//---------------------------------------------------------------------------------------- $login
	/**
	 * @mandatory
	 * @var string
	 */
	public $login;

	//------------------------------------------------------------------------------------- $password
	/**
	 * @mandatory
	 * @password sha1
	 * @var string
	 */
	public $password;

	//---------------------------------------------------------------------------------------- $email
	/**
	 * @mandatory
	 * @var string
	 */
	public $email;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->login);
	}

}
