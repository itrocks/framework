<?php
namespace ITRocks\Framework;

use ITRocks\Framework\Plugin\Activable;
use ITRocks\Framework\Plugin\Configurable;

/**
 * Pass session id thru HTML code using this plugin
 */
class Html_Session implements Activable, Configurable
{

	//------------------------------------------------------------------------------------ USE_COOKIE
	/**
	 * Html session configuration array keys constant
	 */
	const USE_COOKIE = 'use_cookie';

	//----------------------------------------------------------------------------------- $use_cookie
	/**
	 * @var boolean
	 */
	public static bool $use_cookie = false;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $configuration array
	 */
	public function __construct(mixed $configuration)
	{
		self::$use_cookie = isset($configuration[self::USE_COOKIE]) && $configuration[self::USE_COOKIE];
	}

	//-------------------------------------------------------------------------------------- activate
	public function activate() : void
	{
		ini_set('session.use_cookies',      self::$use_cookie);
		ini_set('session.use_only_cookies', false);
		ini_set('session.use_trans_sid',    !self::$use_cookie);
	}

	//------------------------------------------------------------------------------------ useCookies
	/**
	 * @param $cookies boolean|null
	 * @return boolean
	 */
	public static function useCookies(bool $cookies = null) : bool
	{
		if (isset($cookies)) {
			self::$use_cookie = true;
			ini_set('session.use_cookies', $cookies);
		}
		return ini_get('session.use_cookies');
	}

}
