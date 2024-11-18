<?php
namespace ITRocks\Framework\User\Authenticate;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Component\Input;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Locale;
use ITRocks\Framework\Locale\Has_Language;
use ITRocks\Framework\Locale\Language;
use ITRocks\Framework\Mapper\Search_Object;
use ITRocks\Framework\Reflection\Annotation\Property\Password_Annotation;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Session;
use ITRocks\Framework\Tools\Password;
use ITRocks\Framework\Traits\Has_Default;
use ITRocks\Framework\User;
use ITRocks\Framework\User\Active\Has_Active;
use ITRocks\Framework\User\Group;
use ITRocks\Framework\User\Group\Has_Groups;
use ITRocks\Framework\User\Group\Low_Level_Features_Cache;

/**
 * The user authentication class gives direct access to log in, register and disconnect user
 * features.
 */
abstract class Authentication
{

	//---------------------------------------------------------------------------------- authenticate
	/**
	 * Sets user as current for script and session
	 *
	 * Call this to authenticate a user
	 *
	 * @param $user User
	 */
	public static function authenticate(User $user) : void
	{
		User::current($user);
		// TODO LOW if Low_Level_Features_Cache is not used in minimal use, should be moved into feature
		Low_Level_Features_Cache::unsetCurrent();
	}

	//---------------------------------------------------------------------------- controlNameNotUsed
	/**
	 * Control if the name is not already used.
	 *
	 * @param $login string The name of the user
	 * @return boolean true if the login is not used, false if the login is already used.
	 */
	public static function controlNameNotUsed(string $login) : bool
	{
		return !Dao::search(['login' => $login], User::class);
	}

	//----------------------------------------------------------------- controlRegisterFormParameters
	/**
	 * Control if the parameters put in form are right for register
	 *
	 * @param $form array ['login' => $login, 'password' => $password]
	 * @return array A list of errors.
	 */
	public static function controlRegisterFormParameters(array $form) : array
	{
		$errors_messages = [];
		if (trim($form['login']) === '') {
			$errors_messages[] = [
				'name'    => 'Incorrect login',
				'message' => 'The login is incorrect, a login must be not void.'
			];
		}
		if (trim($form['password']) === '') {
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
	 */
	public static function disconnect() : void
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
	public static function getLoginInputs() : array
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
	public static function getRegisterInputs() : array
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
	 * @return ?User
	 */
	public static function login(string $login, string $password) : ?User
	{
		if (!($login && $password)) {
			return null;
		}
		$match = Search_Object::create(User::class);
		/** @noinspection PhpUnhandledExceptionInspection valid constant property for object */
		$property        = new Reflection_Property($match, 'password');
		$match->email    = $login;
		$match->login    = $login;
		$match->password = (new Password($password, Password_Annotation::of($property)->value))
			->encrypted();

		/** @var $users User[] */
		$users = Dao::search(Func::orOp(['email' => $login, 'login' => $login]), User::class);
		foreach ($users as $user) {
			if (static::userMatch($user, $match)) {
				if (isA($user, Has_Language::class)) {
					/** @var $user User|Has_Language */
					Locale::current()->setLanguage($user->language->code);
				}
				return $user;
			}
		}
		return null;
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * Register with current environment using login and password
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $form array The content of the form
	 * @return User
	 */
	public static function register(array $form) : User
	{
		/** @noinspection PhpUnhandledExceptionInspection class */
		$user = Builder::create(User::class);
		/** @noinspection PhpUnhandledExceptionInspection valid constant property for object */
		$property       = new Reflection_Property($user, 'password');
		$user->email    = $form['email'] ?? '';
		$user->login    = $form['login'] ?? '';
		$user->password = (new Password($form['password'], Password_Annotation::of($property)->value))
			->encrypted();
		// TODO LOW should be into a has_groups plugin
		if (isA(Builder::className(Group::class), Has_Default::class) && isA($user, Has_Groups::class)) {
			/** @var $user User|Has_Groups */
			$user->groups = Dao::search(['default' => true], Group::class);
		}
		if (isA($user, Has_Active::class)) {
			/** @var $user User|Has_Active */
			$user->active = true;
		}
		if (isA($user, Has_Language::class)) {
			/** @var $user User|Has_Language */
			$language_code = ($form['language'] ?? '')
				?: lParse(lParse($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '', ','), ';');
			$language = $language_code
				? Dao::searchOne(['code' => $language_code], Language::class)
				: null;
			if (!$language && str_contains($language_code, '-')) {
				$language_code = lParse($language_code, '-');
				$language      = Dao::searchOne(['code' => lParse($language_code, '-')], Language::class);
			}
			if (!$language && isA(Language::class, Has_Default::class)) {
				$language = Dao::searchOne(['default' => true], Language::class);
			}
			if (!$language) {
				$language = Dao::searchOne(['code' => 'en'], Language::class);
			}
			$user->language = $language;
		}
		return Dao::write($user);
	}

	//------------------------------------------------------------------------------------- userMatch
	/**
	 * Returns true if the two users match for authentication
	 *
	 * @param $user  User
	 * @param $match User
	 * @return boolean
	 */
	protected static function userMatch(User $user, User $match) : bool
	{
		return
			(!strcasecmp($match->email, $user->email) || !strcasecmp($match->login, $user->login))
			&& ($match->password === $user->password);
	}

}
