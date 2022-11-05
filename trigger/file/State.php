<?php
namespace ITRocks\Framework\Trigger\File;

use ITRocks\Framework\Trigger\File;

/**
 * Files state, used for file triggers whose $delete_flag_file and $trigger_static are false
 */
abstract class State
{

	//---------------------------------------------------------------------------------------- $files
	/**
	 * @var integer[] ['/my/file/path' => filemtime]
	 */
	public static array $files = [];

	//------------------------------------------------------------------------------------------- get
	/**
	 * Compares the file state date-time and the real file date-time.
	 * Returns true if it differs or if the file was not already stored.
	 *
	 * @param $file File
	 * @return boolean
	 */
	public static function get(File $file) : bool
	{
		if (!isset(static::$files[$file->file_path])) {
			return true;
		}
		return filemtime($file->file_path) !== static::$files[$file->file_path];
	}

	//------------------------------------------------------------------------------------------- set
	/**
	 * Sets a file current date-time into the files list
	 *
	 * @param $file File
	 */
	public static function set(File $file) : void
	{
		static::$files[$file->file_path] = filemtime($file->file_path);
	}

	//----------------------------------------------------------------------------------------- purge
	/**
	 * Purge files that do not exist anymore
	 */
	public static function purge() : void
	{
		foreach (array_keys(static::$files) as $file_path) {
			if (!file_exists($file_path)) {
				unset(static::$files[$file_path]);
			}
		}
	}
	
}
