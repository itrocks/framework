<?php
namespace ITRocks\Framework\User\Last_Connection;

use ITRocks\Framework\Tools\Date_Time;

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
	 * @var Date_Time
	 */
	public $last_connection;

}
