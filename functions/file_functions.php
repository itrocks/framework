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

//--------------------------------------------------------------------------------- findInDirectory
/**
 * Find sub-directories named $search into $directory
 *
 * @param $directory     string
 * @param $search        string
 * @param $limit         integer T_DIR|T_FILE
 * @param $prefix_length integer remove characters from the start of each path (for relative read)
 * @return string[] find paths
 */
function findInDirectory($directory, $search, $limit = T_DIR | T_FILE, $prefix_length = null)
{
	if (substr($directory, -1) === SL) {
		$directory = substr($directory, 0, -1);
	}
	if (!isset($prefix_length)) {
		$prefix_length = strlen($directory);
	}
	$found = [];
	foreach (scandir($directory) as $item) {
		if (in_array($item, [DOT, DOT . DOT])) {
			continue;
		}
		$path   = $directory . SL . $item;
		$is_dir = is_dir($path);
		if (($is_dir && !($limit & T_DIR)) || (is_file($path) && !($limit & T_FILE))) {
			continue;
		}
		if ($item === $search) {
			$found[] = substr($path, $prefix_length);
		}
		if ($is_dir && ($sub_found = findInDirectory($path, $search, $limit, $prefix_length))) {
			$found = array_merge($found, $sub_found);
		}
	}
	return $found;
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
