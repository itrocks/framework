<?php
namespace ITRocks\Framework\User\Last_Connection;

use ITRocks\Framework;
use ITRocks\Framework\Reflection\Attribute\Class_\Extend;
use ITRocks\Framework\Reflection\Attribute\Property\User;
use ITRocks\Framework\Tools\Date_Time;

/**
 * User last connexion date
 */
#[Extend(Framework\User::class)]
trait Has
{

	//------------------------------------------------------------------------------ $last_connection
	#[User(User::READONLY)]
	public Date_Time|string $last_connection;

}
