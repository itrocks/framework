<?php
namespace SAF\Framework;

abstract class User_Authentication
{

	//----------------------------------------------------------------------------------- arrayToUser
	/**
	 * List all of properties to write in user object for the register
	 *
	 * @param $array string[] The form content
	 * @return User A list of properties as "property" => "value"
	 */
	public static function arrayToUser($array)
	{
		$user = Search_Object::newInstance("User");
		$user->login = $array["login"];
		$user->password = Password::crypt(
			$array["password"],
			Reflection_Property::getInstanceOf(get_class($user), "password")
				->getAnnotation("password")->value
		);
		return $user;
	}

	//---------------------------------------------------------------------------------- authenticate
	/**
	 * Sets user as current for script and session
	 *
	 * Call this to authenticate a user
	 *
	 * @param $user User
	 */
	public static function authenticate(User $user)
	{
		User::current($user);
		Session::current()->set($user);
	}

	//---------------------------------------------------------------------------- controlNameNotUsed
	/**
	 * Control if the name is not already used.
	 * @param $login String The name of the user
	 * @return bool True if the login is not used, false if the login is already used.
	 */
	public static function controlNameNotUsed($login)
	{
		$search = Search_Object::newInstance("User");
		$search->login = $login;
		return !Dao::search($search);
	}

	//----------------------------------------------------------------- controlRegisterFormParameters
	/**
	 * Control if the parameters put in form are right for register
	 * @param $form
	 * @return array A list of errors.
	 */
	public static function controlRegisterFormParameters($form)
	{
		$messages = array();
		if(!($form["login"] != "" && str_replace(" ", "", $form["login"]) != "" ))
			$messages[] = array("name" => "Login incorrect",
			                     "message" => "The login is incorrect, a login must be not void.");
		if(!($form["password"] != "" && str_replace(" ", "", $form["password"]) != ""))
			$messages[] = array("name" => "Password incorrect",
			                     "message" => "The password is incorrect, must be not void.");
		return $messages;
	}

	//------------------------------------------------------------------------------------ disconnect
	/**
	 * Remove current user from script and session
	 *
	 * Call this to disconnect user
	 *
	 * @param $user User
	 */
	public static function disconnect(User $user)
	{
		User::current(new User());
		Session::current()->removeAny('SAF\Framework\User');
	}

	//----------------------------------------------------------------------------- getRegisterInputs
	/**
	 * Return the list of the inputs necessary to register
	 *
	 * @return array
	 */
	public static function getRegisterInputs()
	{
		return array(
			array("name" => "login", "type" => "text", "isMultiple" => "false"),
			array("name" => "password", "type" => "password", "isMultiple" => "false"));
	}

	//----------------------------------------------------------------------------------------- login
	/**
	 * Login to current environment using login and password
	 *
	 * Returns logged user if success
	 * To set logger user as current for environment, you must call authenticate()
	 *
	 * @param $login    string
	 * @param $password string
	 * @return User|null
	 */
	public static function login($login, $password)
	{
		$search = Search_Object::newInstance("User");
		$search->login = $login;
		$password = Password::crypt(
			$password,
			Reflection_Property::getInstanceOf(get_class($search), "password")
				->getAnnotation("password")->value
		);
		foreach (Dao::search($search) as $user) {
			if ($user->password === $password) {
				return $user;
			}
		}
		return null;
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * Register with current environment using login and password
	 *
	 * @param $form string[] The form content
	 * @return User user
	 */
	public static function register($form)
	{
		$user = self::arrayToUser($form);
		return Dao::write($user);
	}

}
