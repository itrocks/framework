<?php
namespace ITRocks\Framework\Feature\List_\Search_Parameters_Parser;

use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Feature\List_\Exception;
use ITRocks\Framework\Locale\Loc;

/**
 * Boolean search parameters parser
 */
abstract class Type_Boolean
{

	//----------------------------------------------------------------------------- applyBooleanValue
	/**
	 * @param $search_value string The source search value, as a string typed by the user
	 * @return false|Func\Comparison|Func\Logical|string The resulting dao-ready search expression, or false
	 * @throws Exception
	 */
	public static function applyBooleanValue(string $search_value)
	: bool|Func\Comparison|Func\Logical|string
	{
		$search_value = trim($search_value);
		if ($search_value === '') {
			return '';
		}
		if (Wildcard::hasWildcard($search_value)) {
			$search_value = preg_replace ('/^ \s* [*%?_]+ \s* $/x', '*', $search_value);
			// we cannot have wildcard on boolean type, but we accept expression made of only wildcards
			if (!in_array(trim($search_value), ['*', '%', '?', '_'])) {
				throw new Exception(
					$search_value, Loc::tr('Boolean expression can not have wildcard')
				);
			}
			// only wildcard on a boolean means any value (even if we'd rather do no search on field)
			return Func::orOp([1, 0]);
		}
		if (($search = static::applyBooleanWord($search_value)) !== false) {
			return $search;
		}
		if (is_numeric($search_value)) {
			if ((int)$search_value) {
				return Func::equal('1');
			}
			return Func::equal('0');
		}
		return false;
	}

	//------------------------------------------------------------------------------ applyBooleanWord
	/**
	 * If expression is a boolean word, convert to corresponding boolean value
	 *
	 * @param $expression string The source expression
	 * @return false|Func\Comparison The resulting dao-ready search expression or false if none
	 */
	public static function applyBooleanWord(string $expression) : bool|Func\Comparison
	{
		$word = Words::getCompressedWords([$expression])[0];

		if (in_array($word, static::getBooleanWordsTrueToCompare())) {
			return Func::equal('1');
		}
		elseif (in_array($word, static::getBooleanWordsFalseToCompare())) {
			return Func::equal('0');
		}
		return false;
	}

	//----------------------------------------------------------------------------- getBooleanLetters
	/**
	 * get the char used for translation of 'y' (yes) or 'n' (no) using translation of 'n|y'
	 *
	 * @param $value boolean
	 * @return string a single character
	 */
	private static function getBooleanLetters(bool $value) : string
	{
		static $letters;
		if (!isset($letters)) {
			$letters = explode('|', Loc::tr('n|y'));
			if (!strlen($letters[0])) {
				$letters[0] = 'y';
			}
			if ((count($letters) < 2) || !strlen($letters[1])) {
				$letters[1] = 'n';
			}
			array_splice($letters, 2);
		}
		return $letters[$value ? 1 : 0];
	}

	//----------------------------------------------------------------- getBooleanWordsFalseToCompare
	/**
	 * get the words to compare with a boolean word in search expression
	 *
	 * @return string[]
	 */
	private static function getBooleanWordsFalseToCompare() : array
	{
		static $words;
		if (!isset($words)) {
			$words_references = [_FALSE, NO];
			$words_localized  = [];
			foreach ($words_references as $word) {
				$words_localized[] = Loc::tr($word);
			}
			// We can not translate directly 'n' that is confusing
			$words_references[] = 'n';
			$words_localized[]  = static::getBooleanLetters(false);
			$words = Words::getCompressedWords(array_merge($words_references, $words_localized));
		}
		return $words;
	}

	//------------------------------------------------------------------ getBooleanWordsTrueToCompare
	/**
	 * get the words to compare with a boolean word in search expression
	 *
	 * @return string[]
	 */
	private static function getBooleanWordsTrueToCompare() : array
	{
		static $words;
		if (!isset($words)) {
			$words_references = [YES, _TRUE];
			$words_localized  = [];
			foreach ($words_references as $word) {
				$words_localized[] = Loc::tr($word);
			}
			// We can not translate directly 'y' that is confusing
			$words_references[] = 'y';
			$words_localized[]  = static::getBooleanLetters(true);
			$words = Words::getCompressedWords(array_merge($words_references, $words_localized));
		}
		return $words;
	}

}
