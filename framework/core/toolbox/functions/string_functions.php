<?php

//-------------------------------------------------------------------------------------- lLastParse
/**
 * Renvoie la partie de chaine à gauche de la dernière occurence du séparateur
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
 * Renvoie la partie de chaine à gauche de la première occurence du séparateur
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
 * Renvoie la plus grande longueur de ligne d'un texte dont les lignes sont séparées par '\n'
 *
 * @param $str string
 * @return int
 */
function maxRowLength($str)
{
	$length = 0;
	$rows = explode("\n", $str);
	foreach ($rows as $row) {
		if (strlen($row) > $length) {
			$length = strlen($row);
		}
	}
	return $length;
}

//------------------------------------------------------------------------------------------ mParse
/**
 * Renvoie la partie de la chaîne située entre le délimiteur de début et le délimiteur de fin
 * Si le délimiteur est un tableau, les délimiteurs seront recherchés successivement.
 *
 * @example echo mParse('il a mangé, a bu, a digéré', array(',', 'a '), ',')
 *          recherchera ce qui entre le 'a ' qui est après ',' et le ',' qui suit,
 *          et affichera 'bu'
 * @param $str string
 * @param $begin_sep mixed  array, string
 * @param $end_sep mixed    array, string
 * @param $cnt int
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
 * Returns the part of the string right to the separator
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
 * Renvoie le nombre de lignes dans un texte dont les lignes sont séparées par '\n'
 *
 * @param $str string
 * @return string
 */
function rowCount($str)
{
	return substr_count($str, "\n") + 1;
}

//------------------------------------------------------------------------------------------ rParse
/**
 * Renvoie la partie de chaine à droite de la première occurence du séparateur
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

//------------------------------------------------------------------------------------ strHasAccent
/**
 * Returns true if string has at least one accentued character
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

//------------------------------------------------------------------------------------- strSimplify
/**
 * Returns a very simplified version of string :
 * no space, no accents, no special characters
 * 1/ accents are replaced with non-accentuated characters
 * 2/ string is lowercased
 * 3/ only a..z, 0..9, dot (.,) characters are allowed
 * 4/ not allowed characters are replaced by a joker character, or removed if no joker character is set
 *
 * @param $str      string
 * @param $extended boolean|string|string[] if true, default '.,/- ' is used
 * @param $joker    string if set, replace refused characters with this one
 * @return string
 */
function strSimplify($str, $extended = false, $joker = null)
{
	$str_simplify = array(
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
	);
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
	return strtolower(strSimplify(str_replace(' ', '_', $str), '/-_{}.', $joker));
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
