<?php
namespace ITRocks\Framework\User\Last_Connection;

use ITRocks\Framework\Reflection\Attribute\Class_\Extends_;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\User;

/**
 * User last connexion date
 */
#[Extends_(User::class)]
trait Has
{

	//------------------------------------------------------------------------------ $last_connection
	/**
	 * @link DateTime
	 * @user readonly
	 * @var Date_Time|string
	 */
	public Date_Time|string $last_connection;

}
