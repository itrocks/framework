<?php
namespace SAF\Framework;

/**
 * Application paths functions help you to find out usefull paths of your application
 */
abstract class Paths
{

	//------------------------------------------------------------------------------------ $file_root
	/**
	 * The root path for main script file, without the script name
	 *
	 * @example /var/www/root/path
	 * @var string
	 */
	public static $file_root;

	//------------------------------------------------------------------------------------- $uri_root
	/**
	 * The root path for uri, without the saf launch script name
	 *
	 * @example /root/path
	 * @var string
	 */
	public static $uri_root;

	//---------------------------------------------------------------------------------- $script_name
	/**
	 * the script name, alone, without extension
	 *
	 * @example saf
	 * @var string
	 */
	public static $script_name;

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		$slash  = strrpos($_SERVER["SCRIPT_NAME"], "/") + 1;
		$dotphp = strrpos($_SERVER["SCRIPT_NAME"], ".php");
		self::$file_root = substr(
			$_SERVER["SCRIPT_FILENAME"], 0, strrpos($_SERVER["SCRIPT_FILENAME"], "/") + 1
		);
		self::$script_name = substr($_SERVER["SCRIPT_NAME"], $slash, $dotphp - $slash);
		self::$uri_root = substr($_SERVER["SCRIPT_NAME"], 0, $slash);
	}

}

Paths::register();
