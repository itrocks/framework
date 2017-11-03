<?php
namespace ITRocks\Framework\SSO;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Plugin\Configurable;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\User;

/**
 * Plugin to manage the framework as a SSO authentication server
 *
 */
class Authentication_Server implements Configurable, Registerable
{

	//---------------------------------------------------------------------------------- APPLICATIONS
	const APPLICATIONS = 'applications';

	//------------------------------------------------------------------------------------------ SALT
	const SALT = 'salt';

	//--------------------------------------------------------------------------------- $applications
	/**
	 * The array of external applications that can connect through SSO on this server
	 *
	 * @var Application[]
	 */
	static private $applications;

	//----------------------------------------------------------------------------------------- $salt
	/**
	 * The salt used to build tokens
	 *
	 * @var string
	 */
	static private $salt;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $configuration array
	 */
	public function __construct($configuration)
	{
		if (isset($configuration[self::APPLICATIONS])) {
			foreach ($configuration[self::APPLICATIONS] as $application) {
				$application = Builder::create(Application::class, [$application]);
				if ($application->isValid()) {
					self::$applications[] = $application;
				}
			}
		}
		if (isset($configuration[self::SALT])) {
			self::$salt = $configuration[self::SALT];
		}
	}

	//----------------------------------------------------------------------------- afterAuthenticate
	/**
	 * Compute some SSO properties after a user has been authenticated
	 *
	 * @param $user User
	 */
	public function afterAuthenticate(User $user) {
		// create authentication log
		$authentication = Builder::create(Authentication::class, [
			$user->login,
			Authentication::ACTION_AUTHENTICATE,
			Authentication_Server::buildToken($user)
		]);
		Dao::write($authentication);
	}

	//------------------------------------------------------------------------------ beforeDisconnect
	/**
	 * SSO cleaning before to disconnect a user
	 *
	 */
	public function beforeDisconnect() {
		// create disconnect log
		$user = User::current();
		$authentication = Builder::create(Authentication::class, [
			$user->login,
			Authentication::ACTION_DISCONNECT
		]);
		Dao::write($authentication);
	}

	//------------------------------------------------------------------------------------ buildToken
	/**
	 * Build a token for a user
	 *
	 * @param $user User
	 * @return string
	 */
	public static function buildToken($user)
	{
		return sha1($user->login . session_id() . self::$salt);
	}

	//---------------------------------------------------------------------- getApplicationBySentence
	/**
	 * Get the application that has given sentence
	 *
	 * @param $sentence string
	 * @return Application|null
	 */
	public function getApplicationBySentence($sentence)
	{
		if ($sentence) {
			$application = reset(self::$applications);
			while ($application) {
				if ($application->hasSentence($sentence)) {
					return $application;
				}
				$application = next(self::$applications);
			}
		}
		return null;
	}

	//-------------------------------------------------------------------------------------- getToken
	/**
	 * Get the token for connected user
	 *
	 * @return string
	 */
	public function getToken()
	{
		return self::buildToken(User::current());
	}

	//-------------------------------------------------------------------------------- hasApplication
	/**
	 * Check if application is accessible for connected user
	 *
	 * @param $name string
	 * @return Application|null
	 */
	public function hasApplication($name)
	{
		$application = reset(self::$applications);
		while ($application) {
			if ($application->name == $name) {
				//TODO Evolution: Check sso application is accessible for user (use features and features_groups?)
				return $application;
			}
			$application = next(self::$applications);
		}
		return null;
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * Registration code for the plugin
	 *
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$aop = $register->aop;
		$aop->afterMethod(
			[User\Authenticate\Authentication::class, 'authenticate'],
			[$this, 'afterAuthenticate']
		);
		$aop->beforeMethod(
			[User\Authenticate\Authentication::class, 'disconnect'],
			[$this, 'beforeDisconnect']
		);
	}

	//--------------------------------------------------------------------------------- validateToken
	/**
	 * Validate that token is suitable for login
	 *
	 * @param $application Application
	 * @param $login       string
	 * @param $token       string
	 * @return boolean
	 */
	public function validateToken($application, $login, $token)
	{
		// search for an authentication with token
		$search = [
			'login' => $login,
			'token' => $token,
			'action' => Authentication::ACTION_AUTHENTICATE,
		];
		// with connection more recent than max_session_time of application
		if ($application->max_session_time) {
			$search['request_time_float'] = Dao\Func::greater(time() - $application->max_session_time);
		}
		$sort = Dao::sort('request_time_float');
		$sort->reverse = [true];
		$authentications = Dao::search($search, Authentication::class, [Dao::limit(1), $sort]);
		if ($authentications && count($authentications)) {
			/** @var $authentication Authentication|null */
			$authentication = reset($authentications);
			// search for a disconnection after given
			/** @var $disconnect Authentication */
			$disconnect = Dao::searchOne([
				'login' => $login,
				'action' => Authentication::ACTION_DISCONNECT,
				'request_time_float' => Dao\Func::greater($authentication->request_time_float)
			], Authentication::class);
			if (!$disconnect) {
				return true;
			}
		}
		return false;
	}

}
