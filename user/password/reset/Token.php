<?php
namespace ITRocks\Framework\User\Password\Reset;

use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\User;

#[Store('password_reset_tokens')]
class Token
{

	//----------------------------------------------------------------------------------------- $date
	/**
	 * @default Date_Time::now
	 */
	public Date_Time|string $date;

	//----------------------------------------------------------------------------------------- $done
	public Date_Time|string $done;

	//----------------------------------------------------------------------------------- $identifier
	public string $identifier;

	//--------------------------------------------------------------------------------- $new_password
	/**
	 * @password sha512
	 */
	public string $new_password;

	//----------------------------------------------------------------------------------------- $user
	public User $user;

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return $this->user . SP . Loc::dateToLocale($this->date);
	}
	
}
