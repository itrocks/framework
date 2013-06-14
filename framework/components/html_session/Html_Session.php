<?php
namespace SAF\Framework;

/** @noinspection PhpIncludeInspection */
require_once "framework/core/configuration/Configurable.php";
require_once "framework/core/toolbox/Aop.php";
require_once "framework/core/toolbox/Plugin.php";

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

	//----------------------------------------------------------------------------------- useTransSid
	public static function useTransSid()
	{
		ini_set("session.use_trans_sid", true);
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * always add session id at end of html documents parsing
	 */
	public static function register()
	{
		// PHP configuration method
		if (!self::$registered) {
			self::$registered = true;
			ini_set("session.use_cookies", self::$use_cookie);
			ini_set("session.use_only_cookies", false);
			Aop::add(Aop::BEFORE, "session_start()", array(__CLASS__, "useTransSid"));
		}
	}

}
