<?php
namespace ITRocks\Framework\User\Authenticate;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Component\Input;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Mapper\Search_Object;
use ITRocks\Framework\Reflection\Annotation\Property\Password_Annotation;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Session;
use ITRocks\Framework\Tools\Password;
use ITRocks\Framework\User;

/**
 * The user authentication class gives direct access to login, register and disconnect user features
 */
abstract class Authentication
{

	//----------------------------------------------------------------------------------- arrayToUser
	/**
	 * List all of properties to write in user object for the register
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $array array The form content
	 * @return User A list of properties as 'property' => 'value'
	 */
	public static function arrayToUser(array $array)
	{
		$user = Search_Object::create(User::class);
		/** @noinspection PhpUnhandledExceptionInspection valid constant property for object */
		$property       = new Reflection_Property($user, 'password');
		$user->login    = $array['login'];
		$user->password = (
			new Password($array['password'], Password_Annotation::of($property)->value)
		)->encrypted();
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
	}

	//---------------------------------------------------------------------------- controlNameNotUsed
	/**
	 * Control if the name is not already used.
	 *
	 * @param $login string The name of the user
	 * @return boolean true if the login is not used, false if the login is already used.
	 */
	public static function controlNameNotUsed($login)
	{
		$search        = Search_Object::create(User::class);
		$search->login = $login;
		return !Dao::search($search);
	}

	//----------------------------------------------------------------- controlRegisterFormParameters
	/**
	 * Control if the parameters put in form are right for register
	 *
	 * @param $form array ['login' => $login, 'password' => $password]
	 * @return array A list of errors.
	 */
	public static function controlRegisterFormParameters(array $form)
	{
		$errors_messages = [];
		if (!(($form['login'] != '') && (str_replace(SP, '', $form['login']) != ''))) {
			$errors_messages[] = [
				'name'    => 'Incorrect login',
				'message' => 'The login is incorrect, a login must be not void.'
			];
		}
		if (!(($form['password'] != '') && (str_replace(SP, '', $form['password']) != ''))) {
			$errors_messages[] = [
				'name'    => 'Incorrect password',
				'message' => 'The password is incorrect, must be not void.'
			];
		}
		return $errors_messages;
	}

	//------------------------------------------------------------------------------------ disconnect
	/**
	 * Remove current user from script and session
	 *
	 * Call this to disconnect user
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $user User
	 */
	public static function disconnect()
	{
		User::unsetCurrent();
		Session::current()->removeAny(Builder::className(User::class));
	}

	//-------------------------------------------------------------------------------- getLoginInputs
	/**
	 * Return the list of the inputs necessary to login
	 *
	 * @return array
	 */
	public static function getLoginInputs()
	{
		return Input::newCollection([
			['login',    'login',    'text'],
			['password', 'password', 'password']
		]);
	}

	//----------------------------------------------------------------------------- getRegisterInputs
	/**
	 * Return the list of the inputs necessary to register
	 *
	 * @return array
	 */
	public static function getRegisterInputs()
	{
		return Input::newCollection([
			['login',    'Login',    'text'],
			['password', 'Password', 'password']
		]);
	}

	//----------------------------------------------------------------------------------------- login
	/**
	 * Login to current environment using login and password
	 *
	 * Returns logged user if success
	 * To set logger user as current for environment, you must call authenticate()
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $login    string
	 * @param $password string
	 * @return User|null
	 */
	public static function login($login, $password)
	{
		if (!($login && $password)) {
			return null;
		}

		/** @noinspection PhpUnhandledExceptionInspection valid constant property for object */
		$property = new Reflection_Property(User::class, 'password');
		$password = (new Password($password, Password_Annotation::of($property)->value))->encrypted();

		/** @var $users User[] */
		$users = (strpos($login, AT) ? Dao::search(['email' => $login], User::class) : null)
			?: Dao::search(['login' => $login], User::class);
		foreach ($users as $user) {
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
	 * @param $form array The content of the form
	 * @return User
	 */
	public static function register(array $form)
	{
		$user = static::arrayToUser($form);
		return Dao::write($user);
	}

}
