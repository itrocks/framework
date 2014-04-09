<?php
namespace SAF\Framework;

/**
 * Authenticates a user and launch an authenticate / authenticateError view controller
 */
class User_Authenticate_Controller implements Feature_Controller
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
			User_Authentication::disconnect(User::current());
		}
		$user = User_Authentication::login($form['login'], $form['password']);
		if (isset($user)) {
			User_Authentication::authenticate($user);
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
