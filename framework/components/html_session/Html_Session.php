<?php
namespace SAF\Framework;

/**
 * Pass session id thru HTML code using this plugin
 */
class Html_Session implements Configurable, Activable_Plugin
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
		self::$use_cookie = isset($configuration["use_cookie"]) && $configuration["use_cookie"];
	}

	//-------------------------------------------------------------------------------------- activate
	public function activate()
	{
		ini_set("session.use_cookies", self::$use_cookie);
		ini_set("session.use_only_cookies", false);
		ini_set("session.use_trans_sid", !self::$use_cookie);
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
			ini_set("session.use_cookies", $cookies);
		}
		return ini_get("session.use_cookies");
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * Always add session id at end of html documents parsing
	 *
	 * @param $register Plugin_Register
	 */
	public function register(Plugin_Register $register)
	{
	}

}
