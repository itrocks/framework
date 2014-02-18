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
	file_put_contents($filename, $data);
	if (function_exists('opcache_invalidate') && (substr($filename, -4) == '.php')) {
		opcache_invalidate($filename, true);
	}
}
