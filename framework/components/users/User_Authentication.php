<?php
namespace SAF\Framework;

abstract class User_Authentication
{
	//---------------------------------------------------------------------------------- authenticate
	/**
	 * Sets user as current for script and session
	 *
	 * Called each time a user authenticates
	 *
	 * @param $user User
	 */
	public static function authenticate(User $user)
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
	 * @param $user User
	 */
	public static function disconnect(
		/** @noinspection PhpUnusedParameterInspection needed for plugins or overriding */
		User $user
	) {
		User::current(new User());
		Session::current()->removeAny(__NAMESPACE__ . "\\User");
	}

	//----------------------------------------------------------------------------------------- login
	/**
	 * Login to current environment using login and password
	 *
	 * @param $login string
	 * @param $password string
	 * @return User null if user not found
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
	 * @param $login string
	 * @param $password string
	 * @return User null if user not insert
	 */
	public static function register($login, $password)
	{
		$object = Search_Object::newInstance("User");
		$object->login = $login;
		$object->password = Password::crypt(
			$password,
			Reflection_Property::getInstanceOf(get_class($object), "password")
				->getAnnotation("password")->value
		);
		return Dao::write($object);
	}

	//----------------------------------------------------------------- controlRegisterFormParameters
	/**
	 * Control if the parameters put in form are right for register
	 * @param $form
	 * @return bool false if a form is incorrect
	 */
	public static function controlRegisterFormParameters($form)
	{
		return $form["login"] != "" && $form["password"] != ""
			&& str_replace(" ", "", $form["login"]) != ""
			&& str_replace(" ", "", $form["password"]) != "";
	}

	//----------------------------------------------------------------- controlRegisterFormParameters
	/**
	 * Return the list of the inputs necessary to register
	 * @return array
	 */
	public static function getRegisterInputs(){
		return array(
			array("name" => "login", "type" => "text", "isMultiple" => "false"),
			array("name" => "password", "type" => "password", "isMultiple" => "false"));
	}

	//---------------------------------------------------------------------------- controlNameNotUsed
	/**
	 * Control if the name is not already used.
	 * @param $login String The name of the user
	 * @return bool True if the login is not used, false if the login is already used.
	 */
	public static function controlNameNotUsed($login){
		$search = Search_Object::newInstance("User");
		$search->login = $login;
		if(Dao::search($search))
			return false;
		return true;
	}


}
