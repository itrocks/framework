<?php
namespace ITRocks\Framework\User\Authenticate;

use ITRocks\Framework\Controller\Default_Controller;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Controller\Uri;
use ITRocks\Framework\User;

/**
 * Authenticates a user and launch an authenticate / authenticateError view controller
 */
class Authenticate_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Parameters
	 * @param $form       array an authentication form result with keys 'login' and 'password'
	 * @param $files      array[]
	 * @return mixed
	 */
	public function run(Parameters $parameters, array $form, array $files)
	{
		if (
			isset($form['login']) && is_string($form['login'])
			&& isset($form['password']) && is_string($form['password'])
		) {
			$current = User::current();
			if ($current) {
				Authentication::disconnect(User::current());
			}
			$user = Authentication::login($form['login'], $form['password']);
			if (isset($user)) {
				Authentication::authenticate($user);
				$parameters->set('uri', Uri::previous());
				return (new Default_Controller)->run(
					$parameters, $form, $files, get_class($user), Feature::F_AUTHENTICATE
				);
			}
		}
		$login = $form['login'] ?? '';
		$parameters->set('login', trim($login));
		$parameters->set('focus', boolval(strlen(trim($login))));
		$parameters->set('error', 'error');
		return (new Default_Controller)->run($parameters, $form, $files, User::class, 'login');
	}

}
