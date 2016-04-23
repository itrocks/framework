<?php

//------------------------------------------------------------------------------ script_put_content
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
