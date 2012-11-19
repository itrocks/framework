<?php
namespace SAF\Framework;

class User_Authenticate_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	public function run(Controller_Parameters $parameters, $form, $files)
	{
		$search = Search_Object::newInstance("User");
		$search->login = $form["login"];
		$password = Password::crypt(
			$form["password"],
			Reflection_Property::getInstanceOf(get_class($search), "password")->getAnnotation("password")
		);
		$found = null;
		foreach (Dao::search($search) as $user) {
			if ($user->password === $password) {
				$found = $user;
				break;
			}
		}
		if (isset($found)) {
			User::current($found);
			Session::current()->set($found, "User");
			echo "authenticated !";
		}
		else {
			Session::current()->remove("User");
			echo "user not found !";
		}
		(new Default_Controller())->run(
			$parameters, $form, $files, get_class($search), "authenticate"
		);
	}

}
