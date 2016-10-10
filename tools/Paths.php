<?php
namespace SAF\Framework\Tools;

/**
 * Application paths functions help you to find out usefull paths of your application
 */
abstract class Paths
{

	//------------------------------------------------------------------------------------ $file_root
	/**
	 * The root path for main script file, without the script name
	 *
	 * @example /var/www/root/path/
	 * @example /home/saf/www/
	 * @var string
	 */
	public static $file_root;

	//---------------------------------------------------------------------------------- $project_uri
	/**
	 * The root path for the current project files (direct access, without the saf launch script name)
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
	 * @example saf
	 * @example bappli
	 * @var string
	 */
	public static $script_name;

	//------------------------------------------------------------------------------------- $uri_root
	/**
	 * The root path for uri, without the saf launch script name
	 *
	 * @example /root/path/
	 * @example /
	 * @var string
	 */
	public static $uri_root;

	//------------------------------------------------------------------------------------- $uri_base
	/**
	 * The base uri for creating links between transactions
	 *
	 * @example /root/path/saf
	 * @example /saf
	 * @example /bappli
	 * @var string
	 */
	public static $uri_base;

	//---------------------------------------------------------------------------------------- getUrl
	/**
	 * Get the root URL for the application
	 *
	 * This includes : currently used protocol, server name and uri base
	 * If object or class name is set, path to this object or class name is added to the URL
	 *
	 * @example without class name : 'https://saf.re/saf'
	 * @example with the class name of User : 'https://saf.re/saf/SAF/Framework/User'
	 * @example with a User object of id = 1 : 'https://saf.re/saf/SAF/Framework/User/1'
	 * @param $object object|string object or class name
	 * @return string
	 */
	public static function getUrl($object = null)
	{
		return ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']) ? 'https' : 'http') . '://'
			. $_SERVER['SERVER_NAME']
			. Paths::$uri_base
			. (isset($object) ? (SL . Names::classToUri($object)) : '');
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
	public static function getRelativeFileName($file_name)
	{
		// replace /dir/../ with /
		while (($j = strpos($file_name, '/../')) !== false) {
			$i = strrpos(substr($file_name, 0, $j), SL);
			$file_name = substr($file_name, 0, $i) . substr($file_name, $j + 3);
		}
		// remove /project/root/directory/same/as/current/working/directory/ from beginning of file name
		$current_working_directory = getcwd() . SL;
		$length = strlen($current_working_directory);
		if (substr($file_name, 0, $length) === $current_working_directory) {
			$file_name = substr($file_name, $length);
		}
		return $file_name;
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		$slash  = strrpos($_SERVER['SCRIPT_NAME'], SL) + 1;
		$dotphp = strrpos($_SERVER['SCRIPT_NAME'], '.php');
		self::$file_root = substr(
			$_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'], SL) + 1
		);
		self::$script_name = substr($_SERVER['SCRIPT_NAME'], $slash, $dotphp - $slash);
		self::$uri_root = substr($_SERVER['SCRIPT_NAME'], 0, $slash);
		self::$uri_base = self::$uri_root . self::$script_name;
		self::$project_uri = substr(getcwd(), strlen(self::$file_root) - 1);
	}

}

Paths::register();
