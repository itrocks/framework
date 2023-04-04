<?php
namespace ITRocks\Framework\SSO;

use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Reflection\Attribute\Class_;
use ITRocks\Framework\Reflection\Attribute\Class_\Representative;
use ITRocks\Framework\Reflection\Attribute\Property\Store;
use ITRocks\Framework\Reflection\Attribute\Property\Values;
use ITRocks\Framework\User;

/**
 * Plugin to manage the framework as a SSO authentication server
 *
 * @validate validateAuthentication
 */
#[Representative('action', 'login', 'request_time_float'), Class_\Store]
class Authentication
{

	//----------------------------------------------------------------------------- Actions constants
	const AUTHENTICATE = Feature::F_AUTHENTICATE;
	const DISCONNECT   = Feature::F_DISCONNECT;

	//--------------------------------------------------------------------------------------- $action
	#[Values(self::class)]
	public string $action;

	//---------------------------------------------------------------------------------------- $https
	public bool $https;

	//---------------------------------------------------------------------------------------- $login
	public string $login;

	//-------------------------------------------------------------------------------------- $referer
	public string $referer;

	//------------------------------------------------------------------------------- $remote_address
	public string $remote_address;

	//---------------------------------------------------------------------------------- $remote_host
	public string $remote_host;

	//---------------------------------------------------------------------------------- $remote_port
	public string $remote_port;

	//--------------------------------------------------------------------------- $request_time_float
	public float $request_time_float;

	//----------------------------------------------------------------------------------- $session_id
	public string $session_id;

	//---------------------------------------------------------------------------------------- $token
	public string $token = '';

	//----------------------------------------------------------------------------------------- $user
	#[Store(Store::STRING)]
	public User $user;

	//----------------------------------------------------------------------------------- $user_agent
	public string $user_agent;

	//----------------------------------------------------------------------------------- __construct
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
	public function __toString() : string
	{
		return $this->action = $this->login . SP . $this->action . AT . $this->request_time_float;
	}

	//------------------------------------------------------------------------ validateAuthentication
	public function validateAuthentication() : bool
	{
		return $this->action && $this->login;
	}

}
