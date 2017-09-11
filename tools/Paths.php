<?php
namespace ITRocks\Framework\Tools;

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
	public static $file_root;

	//--------------------------------------------------------------------------------- $project_root
	/**
	 * The root path for the current project files into the file system
	 *
	 * @example /home/vendor/project/environment
	 * @var string
	 */
	public static $project_root;

	//---------------------------------------------------------------------------------- $project_uri
	/**
	 * The root path for the current project files (direct access, without the itrocks launch script name)
	 *
	 * @example /environment/project
	 * @example /test/bappli
	 * @var string
	 */
	public static $project_uri;

	//---------------------------------------------------------------------------------- $script_name
	/**
	 * the script name, alone, without extension
	 *
	 * @example project
	 * @example itrocks
	 * @example bappli
	 * @var string
	 */
	public static $script_name;

	//------------------------------------------------------------------------------------- $uri_base
	/**
	 * The base uri for creating links between transactions
	 *
	 * @example /root/path/itrocks
	 * @example /itrocks
	 * @example /bappli
	 * @var string
	 */
	public static $uri_base;

	//------------------------------------------------------------------------------------- $uri_root
	/**
	 * The root path for uri, without the itrocks launch script name
	 *
	 * @example /root/path/
	 * @example /
	 * @var string
	 */
	public static $uri_root;

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
	public static function getRelativeFileName($file_name)
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
	 * @param $server_name string|null   Environment to use.
	 * @return string
	 */
	public static function getUrl($object = null, $server_name = null)
	{
		return ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']) ? 'https' : 'http') . '://'
			. ($server_name ?: $_SERVER['SERVER_NAME'])
			. Paths::$uri_base
			. (isset($object) ? (SL . Names::classToUri($object)) : '');
	}

	//------------------------------------------------------------------------------------- patchFCGI
	/**
	 * Enable to work with PHP-FPM (FastCGI mode).
	 * This simply change used $_SERVER vars to be libapache2-mod-php compliant
	 *
	 * You must configure fastcgi into apache with this kind of line into your server configuration :
	 * ProxyPassMatch ^\/appname([^\.]*(\.php)?)$ unix:/run/php/php7.1-fpm.sock|fcgi://localhost/path/to/itrocks/framework/index.php
	 */
	protected static function patchFCGI()
	{
		$script                     = explode(SL, $_SERVER['PATH_INFO'])[1];
		$_SERVER['PATH_INFO']       = substr($_SERVER['PATH_INFO'], strlen($script) + 1);
		$_SERVER['SCRIPT_NAME']     = SL . $script . '.php';
		$_SERVER['SCRIPT_FILENAME'] = $_SERVER['DOCUMENT_ROOT'] . $_SERVER['SCRIPT_NAME'];
		$_SERVER['PHP_SELF']        = $_SERVER['SCRIPT_NAME'] . $_SERVER['PATH_INFO'];
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		if (isset($_SERVER['FCGI_ROLE'])) {
			static::patchFCGI();
		}
		$slash   = strrpos($_SERVER['SCRIPT_NAME'], SL) + 1;
		$dot_php = strrpos($_SERVER['SCRIPT_NAME'], '.php');
		$root    = substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'], SL) + 1);
		self::$file_root    = $root;
		self::$project_root = getcwd();
		self::$project_uri  = substr(self::$project_root, strlen($root) - 1);
		self::$script_name  = substr($_SERVER['SCRIPT_NAME'], $slash, $dot_php - $slash);
		self::$uri_root     = substr($_SERVER['SCRIPT_NAME'], 0, $slash);
		self::$uri_base     = self::$uri_root . self::$script_name;
	}

}
