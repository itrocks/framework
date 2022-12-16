<?php
namespace ITRocks\Framework\User\Password\Reset;

use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Mapper\Object_Builder_Array;
use ITRocks\Framework\User\Password;
use ITRocks\Framework\View;
use ITRocks\Framework\View\Html\Template;
use ITRocks\Framework\View\User_Error_Exception;

/**
 * Password reset controller
 */
class Controller implements Feature_Controller
{

	//--------------------------------------------------------------------------------------- FEATURE
	const FEATURE = 'reset';

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @return ?string
	 * @throws User_Error_Exception
	 */
	public function run(Parameters $parameters, array $form, array $files) : ?string
	{
		if (isset($form['email']) && !isset($form['login'])) {
			$form['login'] = $form['email'];
			unset($form['email']);
		}
		if (isset($form['confirm-password']) && !isset($form['password2'])) {
			$form['password2'] = $form['confirm-password'];
			unset($form['confirm-password']);
		}
		$password = $parameters->getMainObject(Password::class);
		if ($token = $parameters->getRawParameter('token')) {
			$template = $password->apply($token) ? 'done' : 'already';
			$parameters->set(Template::TEMPLATE, $template);
		}
		else {
			unset($form['url']);
			(new Object_Builder_Array(Password::class))->build($form, $password);
			if ($password->login && $password->password && $password->password2) {
				$password->reset();
				$parameters->set(Template::TEMPLATE, 'sent');
			}
		}
		$login = $form['login'] ?? '';
		$parameters->set('login', $login);
		$parameters->set('focus', boolval(strlen(trim($login))));
		return View::run($parameters->getObjects(), $form, $files, Password::class, static::FEATURE);
	}

}
