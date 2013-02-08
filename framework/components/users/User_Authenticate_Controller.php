<?php
namespace SAF\Framework;

class User_Authenticate_Controller implements Feature_Controller
{
	//------------------------------------------------------------------------------------------- run
	public function run(Controller_Parameters $parameters, $form, $files)
	{
		$current = User::current();
		if ($current) {
			User_authentication::disconnect(User::current());
		}
		$user = User_authentication::login($form["login"], $form["password"]);
		if (isset($user)) {
			User_authentication::authenticate($user);
			(new Default_Controller())->run(
				$parameters, $form, $files, get_class($user), "authenticate"
			);
		}
		else {
			(new Default_Controller())->run(
				$parameters, $form, $files, "User", "authenticateError"
			);
		}
	}

}
