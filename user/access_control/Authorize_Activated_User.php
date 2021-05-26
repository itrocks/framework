<?php
namespace ITRocks\Framework\User\Access_Control;

use Bappli\Company\Employee;
use ITRocks\Framework\AOP\Joinpoint\Method_Joinpoint;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Session;
use ITRocks\Framework\Tools\Paths;
use ITRocks\Framework\User;
use ITRocks\Framework\User\Authenticate\Authentication;
use ITRocks\Framework\User\Authenticate\Controller;
use ITRocks\Framework\User\Has_Active;
use ITRocks\Framework\View;

/**
 * Class Authorize_Activated_User
 */
class Authorize_Activated_User implements Registerable
{

	//------------------------------------------------------------------------ disconnectDisabledUser
	/**
	 * @param $user User|Has_Active|null
	 * @return boolean
	 */
	private function disconnectDisabledUser(?User $user) : bool
	{
		if ($user === null || $user->isActive()) {return false;}

		if (!$user->isActive()) {
			Authentication::disconnect();
			Session::current()->stop();
			return true;
		}

		return false;

	}

	//---------------------------------------------------------------- onCheckAccessCurrentConnection
	/**
	 * @param $joinpoint Method_Joinpoint
	 */
	public function onCheckAccessCurrentConnection(Method_Joinpoint $joinpoint) : void
	{
		$current  = User::current();

		if ($this->disconnectDisabledUser($current) === true) {
			/** Can't do it with Redirect (redirect to the connection page) */
			echo '<script> location = ' . Q . Paths::$uri_base . Q . '; </script>';
			$joinpoint->stop = true;
		}
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @codeCoverageIgnore
	 * @param $register Register
	 */
	public function register(Register $register) : void
	{
		$register->aop->beforeMethod(
			[Main::class, 'runController'], [$this, 'onCheckAccessCurrentConnection']
		);
	}

}
