<?php
namespace ITRocks\Framework\Webservice;

use ITRocks\Framework\Controller\Default_Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Session;
use ITRocks\Framework\User;
use ITRocks\Framework\User\Authenticate\Authentication;

/**
 * A controller to login for API access of webservices
 */
class Authenticate_Controller implements Default_Feature_Controller
{

	//-------------------------------------------------------------------------------- AUTH constants
	const AUTH_ERROR = 'AUTH_ERROR';
	const AUTH_OK    = 'AUTH=';

	//------------------------------------------------------------------------------------------- run
	/**
	 * Run method for a feature controller working for any class
	 *
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @param $class_name string
	 * @return mixed
	 */
	public function run(Parameters $parameters, array $form, array $files, $class_name)
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
			if (isset($user)) {
				Authentication::authenticate($user);
				return self::AUTH_OK . rParse(Session::sid(), '=');
			}
		}
		return self::AUTH_ERROR;
	}

}
