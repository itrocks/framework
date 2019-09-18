<?php
namespace ITRocks\Framework\User;

use ITRocks\Framework\User\Password\Reset;

/**
 * A password class, to work with passwords (including double-typing)
 */
class Password
{
	use Reset;

	//---------------------------------------------------------------------------------------- $login
	/**
	 * @var string
	 */
	public $login;

	//------------------------------------------------------------------------------------- $password
	/**
	 * @password sha1
	 * @var string
	 */
	public $password;

	//------------------------------------------------------------------------------------ $password2
	/**
	 * @password sha1
	 * @var string
	 */
	public $password2;

}
