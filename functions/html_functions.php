<?php

//-------------------------------------------------------------------------------- htmlEntityDecode
/**
 * html_entity_decode with recursion
 *
 * @param $string   array|string
 * @param $flags    integer|null default is ENT_COMPAT | ENT_HTML401
 * @param $encoding string|null  default is ini_get('default_charset')
 * @return array|string
 */
function htmlEntityDecode(array|string $string, int $flags = null, string $encoding = null)
	: array|string
{
	if (!isset($flags)) {
		$flags = ENT_COMPAT | ENT_HTML401;
	}
	if (!isset($encoding)) {
		$encoding = ini_get('default_charset');
	}
	if (is_array($string)) {
		foreach ($string as $key => $value) {
			$string[$key] = htmlEntityDecode($value, $flags, $encoding);
		}
	}
	else {
		$string = html_entity_decode($string, $flags, $encoding);
	}
	return $string;
}
