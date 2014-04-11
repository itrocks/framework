<?php
namespace SAF\Framework\User\Authenticate;

use SAF\Framework\Controller\Default_Controller;
use SAF\Framework\Controller\Feature_Controller;
use SAF\Framework\Controller\Parameters;
use SAF\Framework\User;

/**
 * Authenticates a user and launch an authenticate / authenticateError view controller
 */
class Authenticate_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Parameters
	 * @param $form       string[] an authentication form result with keys 'login' and 'password'
	 * @param $files      array
	 * @return mixed
	 */
	public function run(Parameters $parameters, $form, $files)
	{
		$current = User::current();
		if ($current) {
			Authentication::disconnect(User::current());
		}
		$user = Authentication::login($form['login'], $form['password']);
		if (isset($user)) {
			Authentication::authenticate($user);
			return (new Default_Controller)->run(
				$parameters, $form, $files, get_class($user), 'authenticate'
			);
		}
		else {
			return (new Default_Controller)->run(
				$parameters, $form, $files, 'User', 'authenticateError'
			);
		}
	}

}
