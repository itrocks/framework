<?php
namespace ITRocks\Framework\User;

use ITRocks\Framework\Traits\Has_Email;

/**
 * An account can connect to an application (or to anything that needs authentication)
 *
 * It has a login, a password, and an email for password recovery automation.
 *
 * @override email @mandatory
 * @store
 */
trait Account
{
	use Has_Email;

	//---------------------------------------------------------------------------------------- $login
	/**
	 * @mandatory
	 * @var string
	 */
	public string $login = '';

	//------------------------------------------------------------------------------------- $password
	/**
	 * @mandatory
	 * @old_password sha1
	 * @password sha512
	 * @var string
	 */
	public string $password = '';

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return $this->login;
	}

}
