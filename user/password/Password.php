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
	public string $login = '';

	//------------------------------------------------------------------------------------- $password
	/**
	 * @password sha512
	 * @var string
	 */
	public string $password = '';

	//------------------------------------------------------------------------------------ $password2
	/**
	 * @password sha512
	 * @var string
	 */
	public string $password2 = '';

}
