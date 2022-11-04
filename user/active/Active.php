<?php
namespace ITRocks\Framework\User;

use ITRocks\Framework\AOP\Joinpoint\Before_Method;
use ITRocks\Framework\Controller;
use ITRocks\Framework\Plugin\Has_Get;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Session;
use ITRocks\Framework\User;
use ITRocks\Framework\User\Active\Has_Active;
use ITRocks\Framework\User\Authenticate\Authentication;

/**
 * @feature Users can be deactivated
 * @feature_build User + Has_Active
 * @priority lowest
 * @see Has_Active, User
 */
class Active implements Registerable
{
	use Has_Get;

	//-------------------------------------------------------------------------------- autoDisconnect
	/**
	 * Disconnect the current user if not active
	 */
	public function autoDisconnect()
	{
		/** @var $user User|Has_Active */
		$user = User::current();
		if ($user && !$user->active) {
			Authentication::disconnect();
			Session::current()->stop();
		}
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register) : void
	{
		$register->aop->afterMethod(
			[Authentication::class, 'userMatch'], [$this, 'resultFalseJoinpoint']
		);
		$register->aop->beforeMethod(
			[Controller\Main::class, 'runController'], [$this, 'autoDisconnect']
		);
		$register->aop->beforeMethod(
			[Password\Reset::class, 'resetUser'], [$this, 'stopCallJoinpoint']
		);
	}

	//-------------------------------------------------------------------------- resultFalseJoinpoint
	/**
	 * Change the result to false if the user is not active
	 *
	 * @param $user   User|Has_Active
	 * @param $result boolean
	 */
	public function resultFalseJoinpoint(User|Has_Active $user, bool &$result)
	{
		if ($result && !$user->active) {
			$result = false;
		}
	}

	//----------------------------------------------------------------------------- stopCallJoinpoint
	/**
	 * Stop a call chain and do not call the original method if the user is not active
	 *
	 * @param $user      User|Has_Active
	 * @param $joinpoint Before_Method
	 */
	public function stopCallJoinpoint(User|Has_Active $user, Before_Method $joinpoint)
	{
		if (!$user->active) {
			$joinpoint->stop = true;
		}
	}

}
