<?php
namespace SAF\Framework;

use SAF\Framework\Plugin\Activable;
use SAF\Framework\Plugin\Configurable;

/**
 * Pass session id thru HTML code using this plugin
 */
class Html_Session implements Activable, Configurable
{

	//----------------------------------------------------------------------------------- $use_cookie
	/**
	 * @var boolean
	 */
	public static $use_cookie = false;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $configuration array
	 */
	public function __construct($configuration)
	{
		self::$use_cookie = isset($configuration['use_cookie']) && $configuration['use_cookie'];
	}

	//-------------------------------------------------------------------------------------- activate
	public function activate()
	{
		ini_set('session.use_cookies',      self::$use_cookie);
		ini_set('session.use_only_cookies', false);
		ini_set('session.use_trans_sid',    !self::$use_cookie);
	}

	//------------------------------------------------------------------------------------ useCookies
	/**
	 * @param $cookies boolean
	 * @return boolean
	 */
	public static function useCookies($cookies = null)
	{
		if (isset($cookies)) {
			self::$use_cookie = true;
			ini_set('session.use_cookies', $cookies);
		}
		return ini_get('session.use_cookies');
	}

}
