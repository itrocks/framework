<?php

//--------------------------------------------------------------------------------- deleteDirectory
/**
 * Deletes a directory, all its subdirectories and all the files they contain
 *
 * @param $directory string
 * @return boolean true on success, or false on failure
 */
function deleteDirectory($directory)
{
	if (!file_exists($directory)) {
		return true;
	}

	if (!is_dir($directory)) {
		return false;
	}

	foreach (array_diff(scandir($directory), ['.', '..']) as $file) {
		if (is_dir($target = ($directory . SL . $file))) {
			deleteDirectory($target);
		}
		else {
			unlink($target);
		}
	}

	return rmdir($directory);
}

//-------------------------------------------------------------------------------- directoryIsEmpty
/**
 * @param $directory string
 * @return boolean
 */
function directoryIsEmpty($directory)
{
	return is_dir($directory) && (count(scandir($directory)) === 2);
}

//------------------------------------------------------------------------------ opcache_invalidate
/**
 * @param $filename string
 */
if (!function_exists('opcache_invalidate')) {
	function opcache_invalidate($filename)
	{
		// if there is no opcache, there is nothing to do
	}
}

//----------------------------------------------------------------------------- script_put_contents
/**
 * Identical than file_put_contents, but must be used instead for PHP files in order to invalidate
 * PHP caching
 *
 * @param $filename string
 * @param $data     string
 */
function script_put_contents($filename, $data)
{
	file_put_contents($filename, $data);
	clearstatcache(true, $filename);
	opcache_invalidate($filename);
}

//---------------------------------------------------------------------------------- unlinkIfExists
/**
 * Unlink a file like unlink() but throws no error if the file did not exist
 *
 * @param $filename string
 * @return boolean|null the unlink() call result (boolean), or null if the file did not exist
 */
function unlinkIfExists($filename)
{
	clearstatcache(true, $filename);
	return file_exists($filename) ? unlink($filename) : null;
}
