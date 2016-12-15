<?php

//-------------------------------------------------------------------------------------- beginsWith
/**
 * Returns true if $haystack begins with $needle
 *
 * @param $haystack string
 * @param $needle   string
 * @return boolean
 */
function beginsWith($haystack, $needle)
{
	$needle_length = strlen($needle);
	return (strlen($haystack) >= $needle_length)
		&& (substr($haystack, 0, $needle_length) === $needle);
}

//---------------------------------------------------------------------------------------- endsWith
/**
 * Returns true if $haystack ends with $needle
 *
 * @param $haystack string
 * @param $needle   string
 * @return boolean
 */
function endsWith($haystack, $needle)
{
	$needle_length = strlen($needle);
	return (strlen($haystack) >= $needle_length)
		&& (substr($haystack, -$needle_length) === $needle);
}

//-------------------------------------------------------------------------------------- lLastParse
/**
 * Returns the part of the string left to the last occurrence of the separator
 *
 * @param $str string
 * @param $sep string
 * @param $cnt int
 * @param $complete_if_not bool
 * @return string
 */
function lLastParse($str, $sep, $cnt = 1, $complete_if_not = true)
{
	if ($cnt > 1) {
		$str = lLastParse($str, $sep, $cnt - 1);
	}
	$i = strrpos($str, $sep);
	if ($i === false) {
		return $complete_if_not ? $str : '';
	}
	else {
		return substr($str, 0, $i);
	}
}

//------------------------------------------------------------------------------------------ lParse
/**
 * Returns the part of the string left to the first occurrence of the separator
 *
 * @param $str string
 * @param $sep string
 * @param $cnt int
 * @param $complete_if_not bool
 * @return string
 */
function lParse($str, $sep, $cnt = 1, $complete_if_not = true)
{
	$i = -1;
	while ($cnt--) {
		$i = strpos($str, $sep, $i + 1);
	}
	if ($i === false) {
		return $complete_if_not ? $str : '';
	}
	else {
		return substr($str, 0, $i);
	}
}

//------------------------------------------------------------------------------------ maxRowLength
/**
 * Returns the wider row characters count (lines are separated by LF = \n = #10)
 *
 * @param $str string
 * @return int
 */
function maxRowLength($str)
{
	$length = 0;
	$rows = explode(LF, $str);
	foreach ($rows as $row) {
		if (strlen($row) > $length) {
			$length = strlen($row);
		}
	}
	return $length;
}

//------------------------------------------------------------------------------------------ mParse
/**
 * Returns the middle part of the string, between $begin_sep and $end_sep
 *
 * If separators are arrays, it will search the first separator, then the next one, etc.
 *
 * @example echo mParse('He eats, drinks and then sleep', [', ', SP], ' then ')
 *          Will result in 'and'
 *          It looks what is after ', ' and then what is after the next space
 *          The returned value stops before ' then '
 * @param $str       string
 * @param $begin_sep string|string[]
 * @param $end_sep   string|string[]
 * @param $cnt       integer
 * @return string
 */
function mParse($str, $begin_sep, $end_sep, $cnt = 1)
{
	// if $begin_sep is an array, rParse each $begin_sep element
	if (is_array($begin_sep)) {
		$sep = array_pop($begin_sep);
		foreach ($begin_sep as $beg) {
			$str = rParse($str, $beg, $cnt);
			$cnt = 1;
		}
		$begin_sep = $sep;
	}
	// if $end_sep is an array, lParse each $end_sep element, starting from the last one
	if (is_array($end_sep)) {
		$end_sep = array_reverse($end_sep);
		$sep = array_pop($end_sep);
		foreach ($end_sep as $end) {
			$str = lParse($str, $end);
		}
		$end_sep = $sep;
	}
	return lParse(rParse($str, $begin_sep, $cnt), $end_sep);
}

//-------------------------------------------------------------------------------------- rLastParse
/**
 * Returns the part of the string right to the last occurrence of the separator
 *
 * @param $str string
 * @param $sep string
 * @param $cnt int
 * @param $complete_if_not bool
 * @return string
 */
function rLastParse($str, $sep, $cnt = 1, $complete_if_not = false)
{
	$i = strrpos($str, $sep);
	while (($cnt > 1) && ($i !== false)) {
		$i = strrpos(substr($str, 0, $i), $sep);
		$cnt--;
	}
	if ($i === false) {
		return $complete_if_not ? $str : '';
	}
	else {
		return substr($str, $i + strlen($sep));
	}
}

//---------------------------------------------------------------------------------------- rowCount
/**
 * Returns the lines count into a text where lines are separated by LF = \n = #10
 *
 * @param $str string
 * @return string
 */
function rowCount($str)
{
	return substr_count($str, LF) + 1;
}

//------------------------------------------------------------------------------------------ rParse
/**
 * Returns the part of the string right to the first occurrence of the separator
 *
 * @param $str             string
 * @param $sep             string
 * @param $cnt             integer
 * @param $complete_if_not boolean
 * @return string
 */
function rParse($str, $sep, $cnt = 1, $complete_if_not = false)
{
	$i = -1;
	while ($cnt--) {
		$i = strpos($str, $sep, $i + 1);
	}
	if ($i === false) {
		return $complete_if_not ? $str : '';
	}
	else {
		return substr($str, $i + strlen($sep));
	}
}

//-------------------------------------------------------------------------------------- strFromUri
/**
 * Returns a default text matching the given URI
 *
 * @param $uri string
 * @return string
 */
function strFromUri($uri)
{
	return str_replace('-', SP, $uri);
}

//------------------------------------------------------------------------------------ strHasAccent
/**
 * Returns true if string has at least one accentuated character
 *
 * @param $str string
 * @return boolean
 */
function strHasAccent($str)
{
	return (strpbrk($str, 'àáâãäåçèéêëìíîïðòóôõöùúûüýÿÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÐÒÓÔÕÖÙÚÛÜÝŸ') !== false);
}

//----------------------------------------------------------------------------------- strIsCapitals
/**
 * Returns true if string contains only capitals letters
 *
 * @param $str string
 * @return boolean
 */
function strIsCapitals($str)
{
	if (!is_string($str)) {
		$str = strval($str);
	}
	// TODO SM a better implementation using a multi-byte string library to take care of any letter
	for ($i = 0; $i < strlen($str); $i ++) {
		if (
			(($str[$i] < 'A') || ($str[$i] > 'Z'))
			&& (strpos('ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÐÒÓÔÕÖÙÚÛÜÝŸ', $str[$i]) === false)
		) {
			return false;
		}
	}
	return !empty($str);
}

//-------------------------------------------------------------------------------------- strReplace
/**
 * Search en replace multiple couples
 *
 * @param $search_replace string[] key is 'search what', value is 'replace with'
 * @param $subject        string The text where to make the replacement
 * @return string
 */
function strReplace($search_replace, $subject)
{
	return str_replace(array_keys($search_replace), array_values($search_replace), $subject);
}

//------------------------------------------------------------------------------------- strSimplify
/**
 * Returns a very simplified version of string :
 * no space, no accents, no special characters
 *
 * 1/ accents are replaced with non-accentuated characters
 * 2/ string is lower-cased
 * 3/ only a..z, A..Z, 0..9, dot (.,) characters are allowed
 * 4/ not allowed characters are replaced by a joker character, or removed if no joker character is
 *    set
 *
 * @param $str      string
 * @param $extended boolean|string|string[] if true, default '.,/- ' is used
 * @param $joker    string if set, replace refused characters with this one
 * @return string
 */
function strSimplify($str, $extended = false, $joker = null)
{
	$str_simplify = [
		'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A',
		'Ç' => 'C',
		'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
		'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
		'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
		'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U',
		'Ý' => 'Y', 'Ÿ' => 'Y',
		'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
		'ç' => 'c',
		'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
		'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
		'ð' => 'o', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
		'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
		'ý' => 'y', 'ÿ' => 'y',
		'&' => 'and'
	];
	$result = '';
	if ($extended && !is_string($extended)) {
		if (is_array($extended)) {
			$extended = join('', $extended);
		}
		else {
			$extended = '.,/- ';
		}
	}
	$str = strtr($str, $str_simplify);
	for ($i = 0; $i < strlen($str); $i ++) {
		$c = $str{$i};
		if (
			(($c >= 'a') && ($c <= 'z')) || (($c >= 'A') && ($c <= 'Z')) || (($c >= '0') && ($c <= '9'))
			|| ($extended && (strpos($extended, $c) !== false))
		) {
			$result .= $c;
		}
		elseif (isset($joker)) {
			$result .= $joker;
		}
	}
	return $result;
}

//------------------------------------------------------------------------------------------ strUri
/**
 * Returns a string as a well formed HTTP URI
 *
 * @param $str   string
 * @param $joker string if set, replace refused characters with this one instead of removing it
 * @return string
 */
function strUri($str, $joker = null)
{
	$uri = strtolower(strSimplify(
		str_replace([BS, Q, SP, ',', ':', ';'], '-', $str), '/-_{}.', $joker
	));
	while (strpos($uri, '--')) {
		$uri = str_replace('--', '-', $uri);
	}
	return $uri;
}

//---------------------------------------------------------------------------------------- ucfirsta
/**
 * Uppercase the first character, even if this is an accented character
 *
 * @param $str string
 * @return string
 */
function ucfirsta($str)
{
	if (!is_string($str)) {
		$str = strval($str);
	}
	if ($str[0] == "\xC3") {
		if (ord($str[1]) >= 160) {
			$str[1] = chr(ord($str[1]) - 32);
		}
		return $str;
	}
	else {
		return ucfirst($str);
	}
}
