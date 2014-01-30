<?php
namespace SAF\Framework;

/**
 * Pass session id thru HTML code using this plugin
 */
class Html_Session implements Configurable, Plugin
{

	//----------------------------------------------------------------------------------- $registered
	/**
	 * @var boolean
	 */
	private static $registered = false;

	//----------------------------------------------------------------------------------- $use_cookie
	/**
	 * @var boolean
	 */
	public static $use_cookie = false;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * The HTML Session contructor can set parameters
	 *
	 * @param null $parameters
	 */
	public function __construct($parameters = null)
	{
		if (is_string($parameters)) {
			$parameters = array($parameters);
		}
		if (is_array($parameters)) {
			if (
				isset($parameters["use_cookie"]) && $parameters["use_cookie"]
				|| in_array("use_cookie", $parameters)
			) {
				self::$use_cookie = true;
			}
		}
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
	 * @param $dealer     Aop_Dealer
	 * @param $parameters array
	 */
	public function register($dealer, $parameters)
	{
		// PHP configuration method
		if (!self::$registered) {
			self::$registered = true;
			ini_set("arg_separator.output", "&amp;");
			ini_set("session.use_cookies", self::$use_cookie);
			ini_set("session.use_only_cookies", false);
			ini_set("session.use_trans_sid", !self::$use_cookie);
		}
	}

}
