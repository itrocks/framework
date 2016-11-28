<?php
namespace ITRocks\Framework\PHP;

use ITRocks\Framework\Application;
use ITRocks\Framework\Tools\Files;
use ITRocks\Framework\Tools\Paths;

/**
 * Abstract class to handle cache directories
 * Any class extending this one should override constant CACHE_DIR_NAME
 */
abstract class Cache
{

	//-------------------------------------------------------------------------------- CACHE_DIR_NAME
	/**
	 * The final directory of cache.
	 *
	 * @example CACHE_DIR_NAME = 'compiled' will give getCacheDir() returns 'cache/compiled'
	 */
	const CACHE_DIR_NAME = '';

	//----------------------------------------------------------------------------------- getCacheDir
	/**
	 * Returns the relative or absolute generator cache dir path (default relative)
	 *
	 * @param $absolute boolean true if want to get absolute path
	 * @return string
	 */
	public static function getCacheDir($absolute = false)
	{
		if (!static::CACHE_DIR_NAME) {
			trigger_error('You must override const CACHE_DIR_NAME in ' . static::class, E_USER_ERROR);
		}
		static $absolute_cache_dir, $relative_cache_dir;
		if (!isset($absolute_cache_dir)) {
			$absolute_cache_dir = Application::current()->getCacheDir() . SL . static::CACHE_DIR_NAME;
			Files::mkdir($absolute_cache_dir);
			$relative_cache_dir = Paths::getRelativeFileName($absolute_cache_dir);
		}
		return $absolute ? $absolute_cache_dir : $relative_cache_dir;
	}

}
