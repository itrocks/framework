<?php
namespace ITRocks\Framework\User\Authenticate;

use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Controller\Uri;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Tools\Paths;
use ITRocks\Framework\User;
use ITRocks\Framework\View;

/**
 * Authenticates a user and launch an authenticate / authenticateError view controller
 */
class Controller implements Feature_Controller
{

	//-------------------------------------------------------------------------------------- reserved
	/**
	 * @param $uri string
	 * @return boolean
	 */
	protected function reserved($uri)
	{
		return (
			beginsWith($uri, View::link(User::class, Feature::F_AUTHENTICATE))
			|| beginsWith($uri, View::link(User::class, Feature::F_DISCONNECT))
			|| beginsWith($uri, View::link(User::class, Feature::F_LOGIN))
		)
			? SL
			: $uri;
	}

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
				Authentication::disconnect();
			}
			$user = Authentication::login($form['login'], $form['password']);
			if ($user) {
				Authentication::authenticate($user);
				if ($form['newToken'] ?? false) {
					$token                    = new Token();
					$token->code              = uniqid('nt', true);
					$token->single_use        = false;
					$token->user              = $user;
					$token->validity_end_date = Date_Time::max();
					Dao::write($token);
					return '[' . $token->code . ']';
				}
				if ($form['refresh'] ?? false) {
					header('Location: ' . Paths::$uri_base . Uri::previous());
				}
				else {
					Main::$current->redirect($this->reserved($form['url'] ?? Uri::previous()));
				}
				return null;
			}
		}
		$login = trim($form['login'] ?? '');
		$parameters->set('error', Loc::tr('The password does not match the entered login'));
		$parameters->set('focus', boolval(strlen($login)));
		$parameters->set('login', $login);
		$parameters->set('url',   $this->reserved($form['url'] ?? Uri::previous()));
		return View::run($parameters->getObjects(), $form, $files, User::class, Feature::F_LOGIN);
	}

}
