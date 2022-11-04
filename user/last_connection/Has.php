<?php
namespace ITRocks\Framework\User\Last_Connection;

use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\User;

/**
 * User last connexion date
 *
 * @extends User
 */
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
