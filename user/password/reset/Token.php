<?php
namespace ITRocks\Framework\User\Password\Reset;

use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\User;

/**
 * @business
 * @store_name password_reset_tokens
 */
class Token
{

	//----------------------------------------------------------------------------------------- $date
	/**
	 * @default Date_Time::now
	 * @link DateTime
	 * @var Date_Time
	 */
	public $date;

	//----------------------------------------------------------------------------------------- $done
	/**
	 * @link DateTime
	 * @var Date_Time
	 */
	public $done;

	//----------------------------------------------------------------------------------- $identifier
	/**
	 * @var string
	 */
	public $identifier;

	//--------------------------------------------------------------------------------- $new_password
	/**
	 * @password sha1
	 * @var string
	 */
	public $new_password;

	//----------------------------------------------------------------------------------------- $user
	/**
	 * @link Object
	 * @var User
	 */
	public $user;

}
