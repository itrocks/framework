<?php
namespace ITRocks\Framework\User\Password\Reset;

use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Attribute\Class_\Store_Name;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\User;

#[Store_Name('password_reset_tokens')]
class Token
{

	//----------------------------------------------------------------------------------------- $date
	/**
	 * @default Date_Time::now
	 * @link DateTime
	 * @var Date_Time|string
	 */
	public Date_Time|string $date;

	//----------------------------------------------------------------------------------------- $done
	/**
	 * @link DateTime
	 * @var Date_Time|string
	 */
	public Date_Time|string $done;

	//----------------------------------------------------------------------------------- $identifier
	/**
	 * @var string
	 */
	public string $identifier;

	//--------------------------------------------------------------------------------- $new_password
	/**
	 * @password sha512
	 * @var string
	 */
	public string $new_password;

	//----------------------------------------------------------------------------------------- $user
	/**
	 * @link Object
	 * @var User
	 */
	public User $user;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return $this->user . SP . Loc::dateToLocale($this->date);
	}
	
}
