<?php
namespace ITRocks\Framework\SSO;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
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

	//---------------------------------------------------------------- Plugin configuration constants
	const APPLICATIONS = 'applications';
	const SALT         = 'salt';

	//--------------------------------------------------------------------------------- $applications
	/**
	 * The array of external applications that can connect through SSO on this server
	 *
	 * @var Application[]
	 */
	static private array $applications;

	//----------------------------------------------------------------------------------------- $salt
	/**
	 * The salt used to build tokens
	 *
	 * @var string
	 */
	static private string $salt;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $configuration array
	 */
	public function __construct(mixed $configuration)
	{
		if (isset($configuration[self::APPLICATIONS])) {
			foreach ($configuration[self::APPLICATIONS] as $application) {
				/** @noinspection PhpUnhandledExceptionInspection constant */
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
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $user User
	 */
	public function afterAuthenticate(User $user) : void
	{
		// create authentication log
		/** @noinspection PhpUnhandledExceptionInspection constant */
		$authentication = Builder::create(Authentication::class, [
			$user->login,
			Authentication::AUTHENTICATE,
			Authentication_Server::buildToken($user)
		]);
		Dao::write($authentication);
	}

	//------------------------------------------------------------------------------ beforeDisconnect
	/**
	 * SSO cleaning before to disconnect a user
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function beforeDisconnect() : void
	{
		// create disconnect log
		$user           = User::current();
		/** @noinspection PhpUnhandledExceptionInspection constant */
		$authentication = Builder::create(Authentication::class, [
			$user->login,
			Authentication::DISCONNECT
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
	public static function buildToken(User $user) : string
	{
		return sha1($user->login . session_id() . self::$salt);
	}

	//---------------------------------------------------------------------- getApplicationBySentence
	/**
	 * Get the application that has given sentence
	 *
	 * @param $sentence string
	 * @return ?Application
	 */
	public function getApplicationBySentence(string $sentence) : ?Application
	{
		if (!$sentence) {
			return null;
		}
		foreach (self::$applications as $application) {
			if ($application->hasSentence($sentence)) {
				return $application;
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
	public function getToken() : string
	{
		return self::buildToken(User::current());
	}

	//-------------------------------------------------------------------------------- hasApplication
	/**
	 * Check if application is accessible for connected user
	 *
	 * @param $name string
	 * @return ?Application
	 */
	public function hasApplication(string $name) : ?Application
	{
		foreach (self::$applications as $application) {
			if ($application->name === $name) {
				// TODO LOWEST Evolution : Check sso application is accessible for user (use features and features_groups ?)
				return $application;
			}
		}
		return null;
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register) : void
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
	public function validateToken(Application $application, string $login, string $token) : bool
	{
		// search for an authentication with token
		$search = [
			'action' => Authentication::AUTHENTICATE,
			'login'  => $login,
			'token'  => $token
		];
		// with connection more recent than max_session_time of application
		if ($application->max_session_time) {
			$search['request_time_float'] = Func::greater(time() - $application->max_session_time);
		}
		$sort            = Dao::sort('request_time_float');
		$sort->reverse   = [true];
		/** @var $authentications Authentication[] */
		$authentications = Dao::search($search, Authentication::class, [Dao::limit(1), $sort]);
		if ($authentications) {
			$authentication = reset($authentications);
			// search for a disconnection after given
			$disconnect = Dao::searchOne([
				'action'             => Authentication::DISCONNECT,
				'login'              => $login,
				'request_time_float' => Func::greater($authentication->request_time_float)
			], Authentication::class);
			if (!$disconnect) {
				return true;
			}
		}
		return false;
	}

}
