<?php
namespace SAF\Framework;

class User_Authenticate_Controller implements Feature_Controller
{
	//------------------------------------------------------------------------------------------- run
	public function run(Controller_Parameters $parameters, $form, $files)
	{
		$current = User::current();
		if ($current) {
			User_Authentication::disconnect(User::current());
		}
		$user = User_Authentication::login($form["login"], $form["password"]);
		if (isset($user)) {
			User_Authentication::authenticate($user);
			return (new Default_Controller())->run(
				$parameters, $form, $files, get_class($user), "authenticate"
			);
		}
		else {
			return (new Default_Controller())->run(
				$parameters, $form, $files, "User", "authenticateError"
			);
		}
	}

}
