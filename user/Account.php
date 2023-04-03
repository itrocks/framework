<?php
namespace ITRocks\Framework\User;

use ITRocks\Framework\Reflection\Attribute\Class_\Override;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Reflection\Attribute\Property\Mandatory;
use ITRocks\Framework\Traits\Has_Email;

/**
 * An account can connect to an application (or to anything that needs authentication)
 *
 * It has a login, a password, and an email for password recovery automation.
 */
#[Override('email', new Mandatory), Store]
trait Account
{
	use Has_Email;

	//---------------------------------------------------------------------------------------- $login
	#[Mandatory]
	public string $login = '';

	//------------------------------------------------------------------------------------- $password
	/**
	 * @old_password sha1
	 * @password sha512
	 */
	#[Mandatory]
	public string $password = '';

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return $this->login;
	}

}
