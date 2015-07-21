<?php

//-------------------------------------------------------------------------------- htmlEntityDecode
/**
 * html_entity_decode with recursion
 *
 * @param $string   string|array
 * @param $flags    integer default is ENT_COMPAT | ENT_HTML401
 * @param $encoding string default is ini_get('default_charset')
 * @return string|array
 */
function htmlEntityDecode($string, $flags = null, $encoding = null)
{
	if (!isset($flags))    $flags = ENT_COMPAT | ENT_HTML401;
	if (!isset($encoding)) $encoding = ini_get('default_charset');
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
