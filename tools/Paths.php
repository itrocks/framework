<?php
namespace ITRocks\Framework\Tools;

use ITRocks\Framework\Session;

/**
 * Application paths functions help you to find out useful paths of your application
 */
abstract class Paths
{

	//------------------------------------------------------------------------------------ $file_root
	/**
	 * The root path for main script file, without the script name
	 *
	 * @example /var/www/root/path/
	 * @example /home/itrocks/www/
	 * @var string
	 */
	public static string $file_root;

	//--------------------------------------------------------------------------------- $project_root
	/**
	 * The root path for the current project files into the file system
	 *
	 * @example /home/vendor/project/environment
	 * @var string
	 */
	public static string $project_root;

	//---------------------------------------------------------------------------------- $project_uri
	/**
	 * The root path for the current project files (direct access, without the itrocks launch script name)
	 *
	 * @example /environment/project
	 * @example /test/bappli
	 * @var string
	 */
	public static string $project_uri;

	//---------------------------------------------------------------------------------- $script_name
	/**
	 * the script name, alone, without extension
	 *
	 * @example project
	 * @example itrocks
	 * @example bappli
	 * @var string
	 */
	public static string $script_name;

	//------------------------------------------------------------------------------------- $uri_base
	/**
	 * The base uri for creating links between transactions
	 *
	 * @example /root/path/itrocks
	 * @example /itrocks
	 * @example /bappli
	 * @var string
	 */
	public static string $uri_base;

	//------------------------------------------------------------------------------------- $uri_root
	/**
	 * The root path for uri, without the itrocks launch script name
	 *
	 * @example /root/path/
	 * @example /
	 * @var string
	 */
	public static string $uri_root;

	//---------------------------------------------------------------------------------- absoluteBase
	/**
	 * @example https://itrocks.org/wiki
	 * @return string
	 */
	public static function absoluteBase() : string
	{
		return static::protocolServer() . static::$uri_base;
	}

	//--------------------------------------------------------------------------- getRelativeFileName
	/**
	 * Normalize the file name :
	 * - always relative, starting from $file_root (remove if was existing)
	 * - replaces ../ using removing of the previous directory name
	 *
	 * @example
	 * Paths::getRelativeFileName('/home/project/path/dir/sub_dir/../sub/file_name.php')
	 * will return 'dir/sub/file_name.php'
	 * @param $file_name string
	 * @return string
	 */
	public static function getRelativeFileName(string $file_name) : string
	{
		// replace /dir/../ with /
		while (($j = strpos($file_name, '/../')) !== false) {
			$i         = strrpos(substr($file_name, 0, $j), SL);
			$file_name = substr($file_name, 0, $i) . substr($file_name, $j + 3);
		}
		// remove /project/root/directory/same/as/current/working/directory/ from beginning of file name
		$current_working_directory = getcwd() . SL;
		$length                    = strlen($current_working_directory);
		if (substr($file_name, 0, $length) === $current_working_directory) {
			$file_name = substr($file_name, $length);
		}
		return $file_name;
	}

	//---------------------------------------------------------------------------------------- getUrl
	/**
	 * Get the root URL for the application.
	 *
	 * This includes : currently used protocol, server name and uri base
	 * If object or class name is set, path to this object or class name is added to the URL
	 *
	 * @example without class name : 'https://itrocks.org/itrocks'
	 * @example with the class name of User : 'https://itrocks.org/itrocks/ITRocks/Framework/User'
	 * @example with a User object of id = 1 : 'https://itrocks.org/itrocks/ITRocks/Framework/User/1'
	 * @param $object      object|string Object or class name.
	 * @param $server_name string        Environment to use.
	 * @return string
	 */
	public static function getUrl(object|string $object = '', string $server_name = '') : string
	{
		return static::protocol() . '://'
			. ($server_name ?: static::server())
			. Paths::$uri_base
			. ($object ? (SL . Names::classToUri($object)) : '');
	}

	//-------------------------------------------------------------------------------------- protocol
	/**
	 * @example https
	 * @return string
	 */
	public static function protocol() : string
	{
		return isset($_SERVER['SERVER_NAME'])
			? (($_SERVER['HTTPS'] ?? false) ? 'https' : 'http')
			: Session::current()->domainScheme();
	}

	//-------------------------------------------------------------------------------- protocolServer
	/**
	 * @example https://itrocks.org
	 * @return string
	 */
	public static function protocolServer() : string
	{
		return isset($_SERVER['SERVER_NAME'])
			? (static::protocol() . '://' . static::server())
			: (Session::current()->domainScheme() . '://' . Session::current()->domainName());
	}

	//-------------------------------------------------------------------------------------- register
	public static function register() : void
	{
		$slash   = strrpos($_SERVER['SCRIPT_NAME'], SL) + 1;
		$dot_php = strrpos($_SERVER['SCRIPT_NAME'], '.php') ?: 1;
		$root    = substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'], SL) + 1);
		self::$file_root    = $root;
		self::$project_root = getcwd();
		self::$project_uri  = substr(self::$project_root, strlen($root) - 1);
		self::$script_name  = substr($_SERVER['SCRIPT_NAME'], $slash, $dot_php - $slash);
		self::$uri_root     = substr($_SERVER['SCRIPT_NAME'], 0, $slash);
		self::$uri_base     = self::$uri_root . self::$script_name;
	}

	//---------------------------------------------------------------------------------------- server
	/**
	 * @example itrocks.org
	 * @return string
	 */
	public static function server() : string
	{
		return $_SERVER['SERVER_NAME'] ?? Session::current()->domainName() ?: 'itrocks.org';
	}

}
