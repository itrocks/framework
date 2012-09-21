<?php
namespace SAF\Framework;

class String
{

	//---------------------------------------------------------------------------------------- $value
	/**
	 * @var string
	 */
	protected $value;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param string $value
	 */
	public function __construct($value)
	{
		$this->value = $value;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->value;
	}

	//----------------------------------------------------------------------------------------- lower
	/**
	 * @return string
	 */
	public function lower()
	{
		return strtolower($this->value);
	}

	//--------------------------------------------------------------------------------------- ucfirst
	/**
	 * @return string
	 */
	public function ucfirst()
	{
		return ucfirst($this->value);
	}

	//--------------------------------------------------------------------------------------- ucwords
	/**
	 * @return string
	 */
	public function ucwords()
	{
		return ucwords($this->value);
	}

	//----------------------------------------------------------------------------------------- upper
	/**
	 * @return string
	 */
	public function upper()
	{
		return strtoupper($this->value);
	}

}

//----------------------------------------------------------------------------- function lLastParse
/**
 * Renvoie la partie de chaine à gauche de la dernière occurence du séparateur
 *
 * @param string $str
 * @param string $sep
 * @param int $cnt
 * @param bool $complete_if_not
 * @return string
 */
function lLastParse($str, $sep, $cnt = 1, $complete_if_not = true)
{
	if ($cnt > 1) {
		$str = lLastParse($str, $sep, $cnt - 1);
	}
	$i = strrpos($str, $sep);
	if ($i === false) {
		return $complete_if_not ? $str : "";
	}
	else {
		return substr($str, 0, $i);
	}
}

//--------------------------------------------------------------------------------- function lParse
/**
 * Renvoie la partie de chaine à gauche de la première occurence du séparateur
 *
 * @param string $str
 * @param string $sep
 * @param int $cnt
 * @param bool $complete_if_not
 * @return string
 */
function lParse($str, $sep, $cnt = 1, $complete_if_not = true)
{
	$i = -1;
	while ($cnt--) {
		$i = strpos($str, $sep, $i + 1);
	}
	if ($i === false) {
		return $complete_if_not ? $str : "";
	}	else {
		return substr($str, 0, $i);
	}
}

//--------------------------------------------------------------------------- function maxRowLength
/**
 * Renvoie la plus grande longueur de ligne d'un texte dont les lignes sont séparées par "\n"
 *
 * @param string $str
 * @return int
 */
function maxRowLength($str)
{
	$length = 0;
	$str = explode("\n", $str);
	foreach ($str as $str) {
		if (strlen($str) > $length) {
			$length = strlen($str);
		}
	}
	return $length;
}

//--------------------------------------------------------------------------------- function mParse
/**
 * Renvoie la partie de la chaîne située entre le délimiteur de début et le délimiteur de fin
 * Si le délimiteur est un tableau, les délimiteurs seront recherchés successivement.
 *
 * @example echo mParse("il a mangé, a bu, a digéré", array(",", "a "), ",")
 *          recherchera ce qui entre le "a " qui est après "," et le "," qui suit,
 *          et affichera "bu"
 * @param string $str
 * @param mixed  $begin_sep array, string
 * @param mixed  $end_sep   array, string
 * @param int    $cnt
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
	// if $end_sep is an array, lärse each $end_sep element, starting from the last one
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

//----------------------------------------------------------------------------- function rLastParse
/**
 * Renvoie la partie de chaine à droite de la dernière occurence du séparateur
 *
 * @param string $str
 * @param string $sep
 * @param int    $cnt
 * @param bool $complete_if_not
 * @return string
 */
function rLastParse($str, $sep, $cnt = 1, $complete_if_not = false)
{
	if ($cnt > 1) {
		$str = lLastParse($str, $sep, $cnt - 1);
	}
	$i = strrpos($str, $sep);
	if ($i === false) {
		return $complete_if_not ? $str : "";
	}
	else {
		return substr($str, $i + strlen($sep));
	}
}

//------------------------------------------------------------------------------- function rowCount
/**
 * Renvoie le nombre de lignes dans un texte dont les lignes sont séparées par "\n"
 *
 * @param string $str
 * @return string
 */
function rowCount($str)
{
	$str = explode("\n", $str);
	return count($str);
}

//--------------------------------------------------------------------------------- function rParse
/**
 * Renvoie la partie de chaine à droite de la première occurence du séparateur
 *
 * @param string $str
 * @param string $sep
 * @param number $cnt
 * @param bool $complete_if_not
 * @return string
 */
function rParse($str, $sep, $cnt = 1, $complete_if_not = false)
{
	$i = -1;
	while ($cnt--) {
		$i = strpos($str, $sep, $i + 1);
	}
	if ($i === false) {
		return $complete_if_not ? $str : "";
	}	else {
		return substr($str, $i + strlen($sep));
	}
}
