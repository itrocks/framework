<?php
namespace ITRocks\Framework\User\Authenticate;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Controller\Uri;
use ITRocks\Framework\Locale\Loc;
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
	 * @return string
	 */
	protected function reserved(string $uri) : string
	{
		return (
			str_starts_with($uri, View::link(User::class, Feature::F_AUTHENTICATE))
			|| str_starts_with($uri, View::link(User::class, Feature::F_DISCONNECT))
			|| str_starts_with($uri, View::link(User::class, Feature::F_LOGIN))
		)
			? SL
			: $uri;
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $parameters Parameters
	 * @param $form       array an authentication form result with keys 'login' and 'password'
	 * @param $files      array[]
	 * @return ?string
	 */
	public function run(Parameters $parameters, array $form, array $files) : ?string
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
					/** @noinspection PhpUnhandledExceptionInspection class */
					$token = Builder::create(By_Token::class)->newToken($user, 'nt', true);
					return 'OK:TOKEN:[' . $token->code . ']';
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
