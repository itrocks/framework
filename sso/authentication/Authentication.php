<?php
namespace ITRocks\Framework\SSO;

use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Dao;
use ITRocks\Framework\User;

/**
 * Plugin to manage the framework as a SSO authentication server
 *
 * @business
 * @representative action, login, request_time_float
 * @validate validateAuthentication
 */
class Authentication
{

	//----------------------------------------------------------------------------- Actions constants
	const AUTHENTICATE = Feature::F_AUTHENTICATE;
	const DISCONNECT   = Feature::F_DISCONNECT;

	//--------------------------------------------------------------------------------------- $action
	/**
	 * @values self::const
	 * @var string
	 */
	public string $action;

	//---------------------------------------------------------------------------------------- $https
	/**
	 * @var boolean
	 */
	public bool $https;

	//---------------------------------------------------------------------------------------- $login
	/**
	 * @var string
	 */
	public string $login;

	//-------------------------------------------------------------------------------------- $referer
	/**
	 * @var string
	 */
	public string $referer;

	//------------------------------------------------------------------------------- $remote_address
	/**
	 * @var string
	 */
	public string $remote_address;

	//---------------------------------------------------------------------------------- $remote_host
	/**
	 * @var string
	 */
	public string $remote_host;

	//---------------------------------------------------------------------------------- $remote_port
	/**
	 * @var string
	 */
	public string $remote_port;

	//--------------------------------------------------------------------------- $request_time_float
	/**
	 * @var float
	 */
	public float $request_time_float;

	//----------------------------------------------------------------------------------- $session_id
	/**
	 * @var string
	 */
	public string $session_id;

	//---------------------------------------------------------------------------------------- $token
	/**
	 * @var string
	 */
	public string $token = '';

	//----------------------------------------------------------------------------------------- $user
	/**
	 * @link Object
	 * @store string
	 * @var User
	 */
	public User $user;

	//----------------------------------------------------------------------------------- $user_agent
	/**
	 * @var string
	 */
	public string $user_agent;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Constructor
	 *
	 * @param $login  string
	 * @param $action string
	 * @param $token  string
	 */
	public function __construct(string $login = '', string $action = '', string $token = '')
	{
		if (!$login) {
			return;
		}

		$this->action = $action;
		$this->login  = $login;
		$this->token  = $token;
		$this->user   = Dao::searchOne(['login' => $login], User::class);

		$this->https              = !empty($_SERVER['HTTPS']);
		$this->referer            = $_SERVER['HTTP_REFERER'] ?? '';
		$this->remote_address     = $_SERVER['REMOTE_ADDR']  ?? '';
		$this->remote_host        = $_SERVER['REMOTE_HOST']  ?? '';
		$this->remote_port        = $_SERVER['REMOTE_PORT']  ?? '';
		$this->request_time_float = $_SERVER['REQUEST_TIME_FLOAT'];
		$this->session_id         = session_id();
		$this->user_agent         = $_SERVER['HTTP_USER_AGENT'] ?? '';
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return $this->action = $this->login . SP . $this->action . AT . $this->request_time_float;
	}

	//------------------------------------------------------------------------ validateAuthentication
	/**
	 * @return boolean
	 */
	public function validateAuthentication() : bool
	{
		return $this->action && $this->login;
	}

}
