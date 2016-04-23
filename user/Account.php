<?php
namespace SAF\Framework\User;

use SAF\Framework\Traits\Has_Email;

/**
 * An account can connect to an application (or to anything that needs authentication)
 *
 * It has a login, a password, and an email for password recovery automation.
 *
 * @business
 * @override email @mandatory
 */
trait Account
{
	use Has_Email;

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

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->login);
	}

}
