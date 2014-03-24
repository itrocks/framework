<?php
namespace SAF\Framework;

/**
 * The user widget controller choose a user output depending on user registration/login state
 *
 * - If no user is logged in, outputs a login / sign-up form
 * - If user is logged in, outputs a logged-in / sign-out form
 */
class User_Widget_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Controller_Parameters
	 * @param $form       array
	 * @param $files      array
	 * @return mixed
	 */
	public function run(Controller_Parameters $parameters, $form, $files)
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
