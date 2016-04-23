<?php
namespace SAF\Framework\Webservice;

use SAF\Framework\Controller\Default_Feature_Controller;
use SAF\Framework\Controller\Parameters;
use SAF\Framework\Session;
use SAF\Framework\User;
use SAF\Framework\User\Authenticate\Authentication;

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
	 * @param $files      array
	 * @param $class_name string
	 * @return mixed
	 */
	public function run(Parameters $parameters, $form, $files, $class_name)
	{
		if (isset($form['login']) && isset($form['password'])) {
			$current = User::current();
			if ($current) {
				Authentication::disconnect(User::current());
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
