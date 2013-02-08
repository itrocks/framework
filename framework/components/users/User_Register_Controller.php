<?php
namespace SAF\Framework;

class User_Register_Controller implements Feature_Controller
{
	//------------------------------------------------------------------------------------------- run
	public function run(Controller_Parameters $parameters, $form, $files)
	{
		$current = User::current();
		if ($current) {
			User_authentication::disconnect(User::current());
		}
		if(isset($form["login"]) && isset($form["password"])){
			if(User_authentication::controlRegisterFormParameters($form)){
				$user = User_authentication::register($form["login"], $form["password"]);
			}
			if($user){
				(new Default_Controller())->run(
					$parameters, $form, $files, "User", "registerConfirm"
				);
			}
			else {
				(new Default_Controller())->run(
					$parameters, $form, $files, "User", "registerError"
				);
			}
		}
		else {
			(new Default_Controller())->run(
				$parameters, $form, $files, "User", "register"
			);
		}
	}

}
