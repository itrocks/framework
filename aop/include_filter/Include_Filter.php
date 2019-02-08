<?php
namespace ITRocks\Framework\AOP;

use ITRocks\Framework\AOP\Include_Filter\Exception;
use ITRocks\Framework\Tools\Paths;
use php_user_filter;

/**
 * A filter to use for each include, require, include_once, require_once call
 * when files eligible to AOP are included
 */
class Include_Filter extends php_user_filter
{

	//------------------------------------------------------------------------------------- CACHE_DIR
	/**
	 * @see \ITRocks\Framework\PHP\Compiler::getCacheDir()
	 */
	const CACHE_DIR = 'cache/compiled';

	//-------------------------------------------------------------------------------------------- ID
	const ID = 'aop.include';

	//------------------------------------------------------------------------------------ $file_name
	/**
	 * The name of the file currently being included
	 * Set by file(), used by filter().
	 *
	 * @var string
	 */
	private static $file_name;

	//------------------------------------------------------------------------------------- cacheFile
	/**
	 * Returns the filename of a cache file for given source file name
	 *
	 * 'a/class/name/like/this/This.php' or 'a/class/name/like/This.php' into
	 * 'a-class-name-like-This'
	 *
	 * @param $file_name string
	 * @return string
	 */
	public static function cacheFile($file_name)
	{
		$dot_pos          = strrpos($file_name, '.');
		$file_name_no_ext = $dot_pos ? substr($file_name, 0, $dot_pos) : $file_name;
		$basename         = basename($file_name_no_ext);
		$parent_dir       = dirname($file_name_no_ext);
		return (strtolower($basename) == basename($parent_dir))
			// case 1 : a/class/name/like/this/This.php => a-class-name-like-This
			? str_replace(SL, '-', dirname($parent_dir) . SL . $basename)
			// case 2 : a/class/name/like/This.php => a-class-name-like-This
			: str_replace(SL, '-', $file_name_no_ext);
	}

	//------------------------------------------------------------------------------------------ file
	/**
	 * Get the real file or filter to include given a file relative to the project root or relative
	 * to the path prefix if given
	 *
	 * @param $file_name   string relative path to the file to be included
	 * @param $path_prefix string
	 * @return string
	 * @throws Exception
	 */
	public static function file($file_name, $path_prefix = '')
	{
		$path_prefix .= (strlen($path_prefix) && (substr($path_prefix, -1)) != SL) ? SL : '';
		// if absolute path given
		if (substr($file_name, 0, 1) === SL) {
			// if project root file, remove this part
			if (strpos($file_name, Paths::$project_root) === 0) {
				$file_name = substr($file_name, strlen(Paths::$project_root) + 1);
				// if path_prefix file, remove this part
				if ($path_prefix && strpos($file_name, $path_prefix) === 0) {
					$file_name = substr($file_name, strlen($path_prefix));
				}
			}
			// not a project file
			else {
				throw new Exception("file outside project is forbidden");
			}
		}
		// now we have relative file, check cache version, using canonical name to avoid /../
		$source_file_name = realpath(Paths::$project_root . SL . $path_prefix . $file_name);
		$cache_file_name
			= Paths::$project_root . SL . self::CACHE_DIR . SL . self::cacheFile($file_name);
		if (file_exists($cache_file_name) && !isset($_POST['Z'])) {
			if (isset($GLOBALS['D'])) {
				return $cache_file_name;
			}
			self::$file_name = $cache_file_name;
			return 'php://filter/read=' . self::ID . '/resource=' . $source_file_name;
		}
		return $source_file_name;
	}

	//---------------------------------------------------------------------------------------- filter
	/**
	 * @param $in       resource
	 * @param $out      resource
	 * @param $consumed integer
	 * @param $closing  boolean
	 * @return integer
	 */
	public function filter($in, $out, &$consumed, $closing)
	{
		while ($bucket = stream_bucket_make_writeable($in)) {
			$consumed = $bucket->datalen;
			if (isset(self::$file_name)) {
				if (!empty($GLOBALS['D'])) {
					echo '- load cached ' . substr(self::$file_name, strlen(Paths::$project_root) + 1) . BRLF;
				}
				$bucket->data    = file_get_contents(self::$file_name);
				$bucket->datalen = strlen($bucket->data);
				/** @noinspection PhpParamsInspection inspector bug */
				stream_bucket_append($out, $bucket);
				self::$file_name = null;
			}
		}
		return PSFS_PASS_ON;
	}

	//----------------------------------------------------------------------------------- getCacheDir
	/**
	 * Returns the cache directory
	 *
	 * @return string
	 */
	public static function getCacheDir()
	{
		return self::CACHE_DIR;
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @return boolean true if well registered
	 */
	public static function register()
	{
		return stream_filter_register(self::ID, __CLASS__) or die('Failed to register filter');
	}

}
