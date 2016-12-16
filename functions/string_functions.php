<?php

//-------------------------------------------------------------------------------------- beginsWith
/**
 * Returns true if $haystack begins with $needle
 * If any (or both) parameter is an array, returns true if any $haystack begins with any $needle
 *
 * @param $haystack string|string[]
 * @param $needle   string|string[]
 * @return boolean
 */
function beginsWith($haystack, $needle)
{
	if (is_array($haystack)) {
		foreach ($haystack as $choice) if (beginsWith($choice, $needle)) return true;
		return false;
	}
	if (is_array($needle)) {
		foreach ($needle as $choice) if (beginsWith($haystack, $choice)) return true;
		return false;
	}
	$needle_length = strlen($needle);
	return (strlen($haystack) >= $needle_length)
		&& (substr($haystack, 0, $needle_length) === $needle);
}

//---------------------------------------------------------------------------------------- endsWith
/**
 * Returns true if $haystack ends with $needle
 * If any (or both) parameter is an array, returns true if any $haystack ends with any $needle
 *
 * @param $haystack string|string[]
 * @param $needle   string|string[]
 * @return boolean
 */
function endsWith($haystack, $needle)
{
	if (is_array($haystack)) {
		foreach ($haystack as $choice) if (endsWith($choice, $needle)) return true;
		return false;
	}
	if (is_array($needle)) {
		foreach ($needle as $choice) if (endsWith($haystack, $choice)) return true;
		return false;
	}
	$needle_length = strlen($needle);
	return (strlen($haystack) >= $needle_length)
		&& (substr($haystack, -$needle_length) === $needle);
}

//-------------------------------------------------------------------------------------- lLastParse
/**
 * Returns the part of the string left to the last occurrence of the separator
 *
 * @param $string          string
 * @param $separator       string
 * @param $count           integer
 * @param $complete_if_not boolean
 * @return string
 */
function lLastParse($string, $separator, $count = 1, $complete_if_not = true)
{
	if ($count > 1) {
		$string = lLastParse($string, $separator, $count - 1);
	}
	$i = strrpos($string, $separator);
	return ($i === false)
		? ($complete_if_not ? $string : '')
		: substr($string, 0, $i);
}

//------------------------------------------------------------------------------------------ lParse
/**
 * Returns the part of the string left to the first occurrence of the separator
 *
 * @param $string          string
 * @param $separator       string
 * @param $count           integer
 * @param $complete_if_not boolean
 * @return string
 */
function lParse($string, $separator, $count = 1, $complete_if_not = true)
{
	$i = -1;
	while ($count--) {
		$i = strpos($string, $separator, $i + 1);
	}
	return ($i === false)
		? ($complete_if_not ? $string : '')
		: substr($string, 0, $i);
}

//------------------------------------------------------------------------------------ maxRowLength
/**
 * Returns the wider row characters count (lines are separated by LF = \n = #10)
 *
 * @param $string string
 * @return integer
 */
function maxRowLength($string)
{
	$length = 0;
	$rows   = explode(LF, $string);
	foreach ($rows as $row) {
		if (strlen($row) > $length) {
			$length = strlen($row);
		}
	}
	return $length;
}

//------------------------------------------------------------------------------------------ mParse
/**
 * Returns the middle part of the string, between $begin_separator and $end_separator
 *
 * If separators are arrays, it will search the first separator, then the next one, etc.
 *
 * @example echo mParse('He eats, drinks and then sleep', [', ', SP], ' then ')
 *          Will result in 'and'
 *          It looks what is after ', ' and then what is after the next space
 *          The returned value stops before ' then '
 * @param $string          string
 * @param $begin_separator string|string[]
 * @param $end_separator   string|string[]
 * @param $count           integer
 * @return string
 */
function mParse($string, $begin_separator, $end_separator, $count = 1)
{
	// if $begin_separator is an array, rParse each $begin_separator element
	if (is_array($begin_separator)) {
		$separator = array_pop($begin_separator);
		foreach ($begin_separator as $begin) {
			$string = rParse($string, $begin, $count);
			$count  = 1;
		}
		$begin_separator = $separator;
	}
	// if $end_separator is an array, lParse each $end_separator element, starting from the last one
	if (is_array($end_separator)) {
		$end_separator = array_reverse($end_separator);
		$separator     = array_pop($end_separator);
		foreach ($end_separator as $end) {
			$string = lParse($string, $end);
		}
		$end_separator = $separator;
	}
	return lParse(rParse($string, $begin_separator, $count), $end_separator);
}

//-------------------------------------------------------------------------------------- rLastParse
/**
 * Returns the part of the string right to the last occurrence of the separator
 *
 * @param $string          string
 * @param $separator       string
 * @param $count           integer
 * @param $complete_if_not boolean
 * @return string
 */
function rLastParse($string, $separator, $count = 1, $complete_if_not = false)
{
	$i = strrpos($string, $separator);
	while (($count > 1) && ($i !== false)) {
		$i = strrpos(substr($string, 0, $i), $separator);
		$count--;
	}
	return ($i === false)
		? ($complete_if_not ? $string : '')
		: substr($string, $i + strlen($separator));
}

//---------------------------------------------------------------------------------------- rowCount
/**
 * Returns the lines count into a text where lines are separated by LF = \n = #10
 *
 * @param $string string
 * @return string
 */
function rowCount($string)
{
	return substr_count($string, LF) + 1;
}

//------------------------------------------------------------------------------------------ rParse
/**
 * Returns the part of the string right to the first occurrence of the separator
 *
 * @param $string          string
 * @param $separator       string
 * @param $count           integer
 * @param $complete_if_not boolean
 * @return string
 */
function rParse($string, $separator, $count = 1, $complete_if_not = false)
{
	$i = -1;
	while ($count--) {
		$i = strpos($string, $separator, $i + 1);
	}
	return ($i === false)
		? ($complete_if_not ? $string : '')
		: substr($string, $i + strlen($separator));
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
 * @param $string string
 * @return boolean
 */
function strHasAccent($string)
{
	return (strpbrk($string, 'àáâãäåçèéêëìíîïðòóôõöùúûüýÿÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÐÒÓÔÕÖÙÚÛÜÝŸ') !== false);
}

//----------------------------------------------------------------------------------- strIsCapitals
/**
 * Returns true if string contains only capitals letters
 *
 * @param $string string
 * @return boolean
 */
function strIsCapitals($string)
{
	if (!is_string($string)) {
		$string = strval($string);
	}
	// TODO SM a better implementation using a multi-byte string library to take care of any letter
	for ($i = 0; $i < strlen($string); $i ++) {
		if (
			(($string[$i] < 'A') || ($string[$i] > 'Z'))
			&& (strpos('ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÐÒÓÔÕÖÙÚÛÜÝŸ', $string[$i]) === false)
		) {
			return false;
		}
	}
	return !empty($string);
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
 * @param $string   string
 * @param $extended boolean|string|string[] if true, default '.,/- ' is used
 * @param $joker    string if set, replace refused characters with this one
 * @return string
 */
function strSimplify($string, $extended = false, $joker = null)
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
	$string = strtr($string, $str_simplify);
	for ($i = 0; $i < strlen($string); $i ++) {
		$c = $string{$i};
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
 * @param $string string
 * @param $joker  string if set, replace refused characters with this one instead of removing it
 * @return string
 */
function strUri($string, $joker = null)
{
	$uri = strtolower(strSimplify(
		str_replace([BS, Q, SP, ',', ':', ';'], '-', $string), '/-_{}.', $joker
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
 * @param $string string
 * @return string
 */
function ucfirsta($string)
{
	if (!is_string($string)) {
		$string = strval($string);
	}
	if ($string[0] == "\xC3") {
		if (ord($string[1]) >= 160) {
			$string[1] = chr(ord($string[1]) - 32);
		}
		return $string;
	}
	else {
		return ucfirst($string);
	}
}
