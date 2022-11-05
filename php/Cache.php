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

	//----------------------------------------------------------------------------------------- $full
	/**
	 * @var boolean if true, it's a full compile
	 */
	public bool $full = false;

	//----------------------------------------------------------------------------------- getCacheDir
	/**
	 * Returns the relative or absolute generator cache dir path (default relative)
	 *
	 * @param $absolute boolean true if you want to get absolute path
	 * @return string
	 */
	public static function getCacheDir(bool $absolute = false) : string
	{
		if (!static::CACHE_DIR_NAME) {
			trigger_error('You must override const CACHE_DIR_NAME in ' . static::class, E_USER_ERROR);
		}
		static $absolute_cache_dir, $relative_cache_dir;
		if (!isset($absolute_cache_dir)) {
			$absolute_cache_dir = Application::getCacheDir() . SL . static::CACHE_DIR_NAME;
			Files::mkdir($absolute_cache_dir);
			$relative_cache_dir = Paths::getRelativeFileName($absolute_cache_dir);
		}
		return $absolute ? $absolute_cache_dir : $relative_cache_dir;
	}

	//--------------------------------------------------------------------------- manageCacheDirReset
	/**
	 * Reset the cache directory if required
	 */
	public function manageCacheDirReset() : void
	{
		if ($this->full) {
			$absolute_cache_dir = static::getCacheDir(true);
			if ($absolute_cache_dir && is_dir($absolute_cache_dir)) {
				system('rm -rf ' . $absolute_cache_dir . '/*');
			}
		}
	}

}
