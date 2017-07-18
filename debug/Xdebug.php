<?php
namespace ITRocks\Framework\Debug;

use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;

/**
 * The Xdebug plugin disable XDEBUG_SESSION_START and KEY get vars to avoid side effects
 */
class Xdebug implements Registerable
{

	//-------------------------------------------------------------------- xdebug parameter constants
	const KEY           = 'KEY';
	const PROFILE       = 'XDEBUG_PROFILE';
	const SESSION       = 'XDEBUG_SESSION';
	const SESSION_START = 'XDEBUG_SESSION_START';

	//------------------------------------------------------------------------------------------ $key
	/**
	 * @var string
	 */
	protected $key;

	//-------------------------------------------------------------------------------------- $profile
	/**
	 * @var string
	 */
	protected $profile;

	//-------------------------------------------------------------------------------- $session_start
	/**
	 * @var string
	 */
	protected $session_start;

	//-------------------------------------------------------------------------------------- addToUri
	/**
	 * Adds xdebug data to the uri, if there were some some
	 *
	 * @param $uri string
	 * @return string
	 */
	public function addToUri($uri)
	{
		if (isset($this->profile)) {
			$uri = $this->append($uri, self::PROFILE, $this->profile);
		}
		if (isset($this->session_start)) {
			$uri = $this->append($uri, self::SESSION_START, $this->session_start);
		}
		if (isset($this->key)) {
			$uri = $this->append($uri, self::KEY, $this->key);
		}
		if (isset($_COOKIE[self::SESSION])) {

		}
		return $uri;
	}


	/**
	 * @param $uri   string
	 * @param $key   string
	 * @param $value string
	 * @return string;
	 */
	protected function append($uri, $key, $value)
	{
		return $uri . (strpos($uri, '?') !== false ? '&' : '?') . $key . '=' . $value;
	}

	//--------------------------------------------------------------------------------------- cleanup
	/**
	 * @param $get string[]
	 */
	public function cleanup(array &$get)
	{
		if (isset($get[self::PROFILE])) {
			$this->profile = $get[self::PROFILE];
			unset($get[self::PROFILE]);
		}
		if (isset($get[self::SESSION_START])) {
			$this->session_start = $get[self::SESSION_START];
			unset($get[self::SESSION_START]);
			if (isset($get[self::KEY])) {
				$this->key = $get[self::KEY];
				unset($get[self::KEY]);
			}
		}
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param Register $register
	 */
	public function register(Register $register)
	{
		$aop = $register->aop;
		$aop->beforeMethod([Main::class, 'runController'], [$this, 'cleanup']);
	}

}
