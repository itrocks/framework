<?php
namespace SAF\AOP;

use php_user_filter;

/**
 * A filter to use for each include, require, include_once, require_once call
 * when files eligible to AOP are included
 */
class Include_Filter extends php_user_filter
{

	const ID = 'aop.include';

	//------------------------------------------------------------------------------------ $cache_dir
	/**
	 * @var string
	 */
	private static $cache_dir;

	//------------------------------------------------------------------------------------ $file_name
	/**
	 * The name of the file currently being included
	 * Set by file(), used by filter().
	 *
	 * @var string
	 */
	private static $file_name;

	//-------------------------------------------------------------------------------------- register
	/**
	 * @return boolean true if well registered
	 */
	public static function register()
	{
		self::$cache_dir = substr(
			$_SERVER['SCRIPT_NAME'], strrpos($_SERVER['SCRIPT_NAME'], '/') + 1, -4
		) . '/cache/aop';
		return stream_filter_register(self::ID, __CLASS__);
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
				$bucket->data = file_get_contents(self::$file_name);
				$bucket->datalen = strlen($bucket->data);
				/** @noinspection PhpParamsInspection inspector bug */
				stream_bucket_append($out, $bucket);
				self::$file_name = null;
			}
		}
		return PSFS_PASS_ON;
	}

	//------------------------------------------------------------------------------------------ file
	/**
	 * @param $file_name string relative path to the file to be included
	 * @return string
	 */
	public static function file($file_name)
	{
		if (isset($_SERVER['ENV']) && ($_SERVER['ENV'] == 'DEV')) {
			$cache_file_name = self::$cache_dir . '/' . str_replace('/', '-', substr($file_name, 0, -4));
			if (file_exists($cache_file_name)) {
				self::$file_name = $cache_file_name;
				return 'php://filter/read=' . self::ID . '/resource=' . $file_name;
			}
		}
		return $file_name;
	}

}
