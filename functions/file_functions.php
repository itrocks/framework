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
	if (file_put_contents($filename, $data)) {
		if (function_exists('opcache_invalidate') && (substr($filename, -4) == '.php')) {
			/** @noinspection PhpComposerExtensionStubsInspection function_exists called */
			opcache_invalidate($filename, true);
		}
	}
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
	if (file_exists($filename)) {
		return unlink($filename);
	}
	else {
		return null;
	}
}
