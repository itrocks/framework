<?php

//--------------------------------------------------------------------------- base64_decode_urlsafe
/**
 * Decodes an URL-safe base64 encoded string
 *
 * @param $string string
 * @return string
 */
function base64_decode_url_safe($string)
{
	return base64_decode(strtr($string, ['-' => '+', '_' => '/', '.' => '=']));
}

//--------------------------------------------------------------------------- base64_encode_urlsafe
/**
 * Encodes data to a string in an URL-safe version of base64
 *
 * @param $string string
 * @return string
 */
function base64_encode_url_safe($string)
{
	return strtr(base64_encode($string), ['+' => '-', '/' => '_', '=' => '.']);
}

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

//------------------------------------------------------------------------------------- cleanSpaces
/**
 * trim spaces, remove double-spaces, replace tabs by spaces
 *
 * @param $string string
 * @return string
 */
function cleanSpaces($string)
{
	$string = str_replace(TAB, SP, trim($string));
	while (strpos($string, SP . SP)) {
		$string = str_replace(SP . SP, SP, $string);
	}
	return $string;
}

//---------------------------------------------------------------------------------------- contains
/**
 * Return true if $string contains $substring_to_search, false otherwise
 *
 * @param $string              string
 * @param $substring_to_search string string
 * @return boolean
 */
function contains($string, $substring_to_search)
{
	return (strpos($string, $substring_to_search) !== false);
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

//------------------------------------------------------------------------- htmlSpecialCharsRecurse
/**
 * @param $value string|string[]
 * @return string|string[]
 */
function htmlSpecialCharsRecurse($value)
{
	return is_array($value)
		? array_map('htmlSpecialCharsRecurse', $value)
		: htmlspecialchars($value);
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

//----------------------------------------------------------------------------------- removeAccents
/**
 * Replace accents by the closest char in the given string.
 *
 * @param $string string The string to remove accents in
 * @return string
 */
function removeAccents($string)
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

	return strtr($string, $str_simplify);
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

//-------------------------------------------------------------------------------------- strFlexCmp
/**
 * Compare two strings, replace accents by the equivalent non-accentuated character
 *
 * @param $string1 string
 * @param $string2 string
 * @return boolean
 */
function strFlexCmp($string1, $string2)
{
	return strcasecmp(removeAccents($string1), removeAccents($string2));
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
 * Search en replace multiple couples with a [$search => $replace] associative notation
 *
 * @param $search_replace string[] key is 'search what', value is 'replace with'
 * @param $subject        string|string[] The text(s) where to make the replacement into
 * @return string|string[] The subject with applied replacements
 */
function strReplace(array $search_replace, $subject)
{
	$search  = array_keys($search_replace);
	$replace = array_values($search_replace);
	return is_array($subject)
		? strReplaceArray($search, $replace, $subject)
		: str_replace($search, $replace, $subject);
}

//--------------------------------------------------------------------------------- strReplaceArray
/**
 * Search and replace into multiple subjects
 *
 * This is the same as str_replace, but with multiple subjects.
 *
 * @param $search   string|string[] The searched string(s)
 * @param $replace  string|string[] The replacement string(s)
 * @param $subjects string[]
 * @return string[]
 * @see str_replace
 */
function strReplaceArray($search, $replace, array $subjects)
{
	foreach ($subjects as $key => $subject) {
		$subjects[$key] = is_array($subject)
			? strReplaceArray($search, $replace, $subject)
			: str_replace($search, $replace, $subject);
	}
	return $subjects;
}

//-------------------------------------------------------------------------------------- strReplace
/**
 * Search and replace values until all instances were replaced. Loop if there are still values to
 * be replaced after each search-and-replace
 *
 * @example strReplaceLoop(['--' => '-'], 'a---text--with-dashes) => 'a-text-with-dashes'
 * @param $search_replace string[] key is the searched value, value is the replacement value
 * @param $subject        string   the text where to make the replacement into
 * @return string the subject with applied replacements
 */
function strReplaceLoop(array $search_replace, $subject)
{
	do {
		$found = false;
		foreach ($search_replace as $search => $replace) {
			if (!$found && (strpos($subject, $search) !== false)) {
				$found = true;
				break;
			}
			if ($found) {
				$subject = str_replace($search, $replace, $subject);
			}
		}
	}
	while ($found);
	return $subject;
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
	$result = '';
	if ($extended && !is_string($extended)) {
		if (is_array($extended)) {
			$extended = join('', $extended);
		}
		else {
			$extended = '.,/- ';
		}
	}

	$string = removeAccents($string);

	for ($i = 0; $i < strlen($string); $i ++) {
		$c = $string[$i];
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

//----------------------------------------------------------------------------------- strUriElement
/**
 * @param $string string
 * @param $joker  string
 * @return string
 */
function strUriElement($string, $joker = null)
{
	return str_replace(SL, '-', strUri($string, $joker));
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

//------------------------------------------------------------------------------------------- words
/**
 * @param $string    string
 * @param $lowercase boolean lowercase all words eg to get an unique version of 'Word' and 'word'
 * @return string[]
 */
function words($string, $lowercase = false)
{
	/** @noinspection SpellCheckingInspection this is not text */
	static $word_separators = '²&~"#\'{([-|`_\\^@°)]+=}$£¤%*µ<>,?;.:/!§';
	$words = explode(SP, str_replace(str_split($word_separators), SP, $string));
	if ($lowercase) {
		foreach ($words as &$word) {
			$word = strtolower($word);
		}
	}
	$words     = array_unique($words);
	$empty_key = array_search('', $words, true);
	if ($empty_key !== false) {
		unset($words[$empty_key]);
	}
	return $words;
}
