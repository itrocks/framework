<?php
namespace ITRocks\Framework\User;

use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\User;
use ITRocks\Framework\View;

/**
 * The user widget controller choose a user output depending on user registration/login state
 *
 * - If no user is logged in, outputs a login / sign-up form
 * - If user is logged in, outputs a logged-in / sign-out form
 */
class Widget_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array
	 * @return mixed
	 */
	public function run(Parameters $parameters, $form, $files)
	{
		$parameters = $parameters->getObjects();
		if ($user = User::current()) {
			array_unshift($parameters, $user);
			return View::run($parameters, $form, $files, get_class($user), 'display');
		}
		else {
			$user = new User();
			array_unshift($parameters, $user);
			return View::run($parameters, $form, $files, get_class($user), 'login');
		}
	}

}
