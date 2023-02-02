<?php
namespace ITRocks\Framework\User\Last_Connection;

use ITRocks\Framework\Reflection\Attribute\Class_\Extend;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\User;

/**
 * User last connexion date
 */
#[Extend(User::class)]
trait Has
{

	//------------------------------------------------------------------------------ $last_connection
	/**
	 * @user readonly
	 */
	public Date_Time|string $last_connection;

}
