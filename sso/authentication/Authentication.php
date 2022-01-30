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
	public $action;

	//---------------------------------------------------------------------------------------- $https
	/**
	 * @var boolean
	 */
	public $https;

	//---------------------------------------------------------------------------------------- $login
	/**
	 * @var string
	 */
	public $login;

	//-------------------------------------------------------------------------------------- $referer
	/**
	 * @var string
	 */
	public $referer;

	//------------------------------------------------------------------------------- $remote_address
	/**
	 * @var string
	 */
	public $remote_address;

	//---------------------------------------------------------------------------------- $remote_host
	/**
	 * @var string
	 */
	public $remote_host;

	//---------------------------------------------------------------------------------- $remote_port
	/**
	 * @var string
	 */
	public $remote_port;

	//--------------------------------------------------------------------------- $request_time_float
	/**
	 * @var float
	 */
	public $request_time_float;

	//----------------------------------------------------------------------------------- $session_id
	/**
	 * @var string
	 */
	public $session_id;

	//---------------------------------------------------------------------------------------- $token
	/**
	 * @var string
	 */
	public $token = '';

	//----------------------------------------------------------------------------------------- $user
	/**
	 * @link Object
	 * @store string
	 * @var User
	 */
	public $user;

	//----------------------------------------------------------------------------------- $user_agent
	/**
	 * @var string
	 */
	public $user_agent;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Constructor
	 *
	 * @param $login  string
	 * @param $action string
	 * @param $token  string
	 */
	public function __construct($login = null, $action = null, $token = null)
	{
		if (isset($login)) {
			$this->login = $login;
			$this->user  = Dao::searchOne(['login' => $login], User::class);
			if (isset($action)) {
				$this->action = $action;
			}
			if (isset($token)) {
				$this->token = $token;
			}
			$this->https              = !empty($_SERVER['HTTPS']);
			$this->referer            = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
			$this->remote_address     = isset($_SERVER['REMOTE_ADDR'])  ? $_SERVER['REMOTE_ADDR']  : '';
			$this->remote_host        = isset($_SERVER['REMOTE_HOST'])  ? $_SERVER['REMOTE_HOST']  : '';
			$this->remote_port        = isset($_SERVER['REMOTE_PORT'])  ? $_SERVER['REMOTE_PORT']  : '';
			$this->request_time_float = $_SERVER['REQUEST_TIME_FLOAT'];
			$this->session_id         = session_id();
			$this->user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
		}
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
	public function validateAuthentication()
	{
		return $this->action && $this->login;
	}

}
