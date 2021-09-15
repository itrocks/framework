<?php
namespace ITRocks\Framework\User;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\User;
use ITRocks\Framework\User\Authenticate\Authentication;
use ITRocks\Framework\User\Last_Connection\Has;

/**
 * @feature Record and display user last connection date
 * @feature_build User + Has
 * @see Has, User
 */
class Last_Connection implements Registerable
{

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$register->aop->afterMethod(
			[Authentication::class, 'authenticate'], [$this, 'saveLastConnectionDate']
		);
	}

	//------------------------------------------------------------------------ saveLastConnectionDate
	/**
	 * @param $user User|Has
	 */
	public function saveLastConnectionDate(User|Has $user)
	{
		$user->last_connection = Date_Time::now();
		Dao::write($user, Dao::only('last_connection'));
	}

}
