<?php
namespace SAF\Framework;

class User_Authenticate_Controller implements Feature_Controller
{

	//---------------------------------------------------------------------------------- authenticate
	/**
	 * Sets user as current for script and session
	 *
	 * Called each time a user authenticates
	 *
	 * @param User $user
	 */
	private function authenticate(User $user)
	{
		User::current($user);
		Session::current()->set($user);
	}

	//------------------------------------------------------------------------------------ disconnect
	/**
	 * Remove current user from script and session
	 *
	 * Called each time a user disconnects
	 *
	 * @param User $user
	 */
	private function disconnect(User $user)
	{
		User::current(new User());
		Session::current()->removeAny(__NAMESPACE__ . "\\User");
	}

	//----------------------------------------------------------------------------------------- login
	/**
	 * Login to current environment using login and password
	 * 
	 * @param string $login
	 * @param string $password
	 * @return User null if user not found
	 */
	private function login($login, $password)
	{
		$search = Search_Object::newInstance("User");
		$search->login = $login;
		$password = Password::crypt(
			$password,
			Reflection_Property::getInstanceOf(get_class($search), "password")->getAnnotation("password")
		);
		foreach (Dao::search($search) as $user) {
			if ($user->password === $password) {
				return $user;
			}
		}
		return null; 
	}

	//------------------------------------------------------------------------------------------- run
	public function run(Controller_Parameters $parameters, $form, $files)
	{
		$current = User::current();
		if ($current) {
			$this->disconnect(User::current());
		}
		$user = $this->login($form["login"], $form["password"]);
		if (isset($user)) {
			$this->authenticate($user);
			(new Default_Controller())->run(
				$parameters, $form, $files, get_class($user), "authenticate"
			);
		}
		else {
			(new Default_Controller())->run(
				$parameters, $form, $files, get_class($user), "authenticateError"
			);
		}
	}

}
