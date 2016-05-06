<?php
namespace SAF\Framework\Widget\Data_List\Search_Parameters_Parser;

use SAF\Framework\Dao\Func;
use SAF\Framework\Dao\Option;
use SAF\Framework\Locale\Loc;
use SAF\Framework\Widget\Data_List\Data_List_Exception;

/**
 * Boolean search parameters parser
 *
 * @extends Search_Parameter_Parser
 */
trait Type_Boolean
{

	//----------------------------------------------------------------------------- applyBooleanValue
	/**
	 * @param $search_value string|Option
	 * @return mixed
	 * @throws Data_List_Exception
	 */
	protected function applyBooleanValue($search_value)
	{
		$search_value = trim($search_value);
		if (!strlen($search_value)) {
			return '';
		}
		if ($this->hasJoker($search_value)) {
			$search_value = preg_replace ('/^ \s* [*%?_]+ \s* $/x', '*', $search_value);
			// we cannot have wildcard on boolean type, but we accept expression made of only wildcards
			if (!in_array(trim($search_value), ['*', '%', '?', '_'])) {
				throw new Data_List_Exception(
					$search_value, Loc::tr('Boolean expression can not have wildcard')
				);
			}
			// only wildcard on a boolean means any value (even if we'd rather do no search on field)
			return Func::orOp([1, 0]);
		}
		if (($search = $this->applyBooleanWord($search_value)) !== false) {
			return $search;
		}
		if (is_numeric($search_value)) {
			if ((int)$search_value) {
				return Func::equal("1");
			}
			return Func::equal("0");
		}
		return false;
	}

	//------------------------------------------------------------------------------ applyBooleanWord
	/**
	 * If expression is a boolean word, convert to corresponding boolean value
	 * @param $expr          string
	 * @return mixed|boolean false
	 */
	protected function applyBooleanWord($expr)
	{
		$word = $this->getCompressedWords([$expr])[0];

		if (in_array($word, $this->getBooleanWordsTrueToCompare())) {
			return Func::equal("1");
		}
		elseif (in_array($word, $this->getBooleanWordsFalseToCompare())) {
			return Func::equal("0");
		}
		return false;
	}


	//----------------------------------------------------------------------------- getBooleanLetters
	/**
	 * get the char used for translation of 'y' (yes) or 'n' (no) using translation of 'n|y'
	 *
	 * @param $value boolean
	 * @return string of single char
	 */
	function getBooleanLetters($value)
	{
		static $letters;
		if (!isset($letters)) {
			$letters = explode('|', Loc::tr('n|y'));
			if (!strlen($letters[0])) {
				$letters[0] = 'y';
			}
			if (count($letters)<2 || !strlen($letters[1])) {
				$letters[1] = 'n';
			}
			array_splice($letters, 2);
		}
		return $letters[($value?1:0)];
	}

	//----------------------------------------------------------------- getBooleanWordsFalseToCompare
	/**
	 * get the words to compare with a boolean word in search expression
	 *
	 * @return array
	 */
	protected function getBooleanWordsFalseToCompare()
	{
		static $words_references = ['no', 'false'];
		$words_localized  = [];
		foreach($words_references as $word) {
			$words_localized[] = Loc::tr($word);
		}
		// We can not translate directly 'n' that is confusing
		$words_references[] = 'n';
		$words_localized[] = $this->getBooleanLetters(false);
		return $this->getCompressedWords(array_merge($words_references, $words_localized));
	}

	//------------------------------------------------------------------ getBooleanWordsTrueToCompare
	/**
	 * get the words to compare with a boolean word in search expression
	 *
	 * @return array
	 */
	protected function getBooleanWordsTrueToCompare()
	{
		static $words_references = ['yes', 'true'];
		$words_localized  = [];
		foreach($words_references as $word) {
			$words_localized[] = Loc::tr($word);
		}
		// We can not translate directly 'y' that is confusing
		$words_references[] = 'y';
		$words_localized[] = $this->getBooleanLetters(true);
		return $this->getCompressedWords(array_merge($words_references, $words_localized));
	}

}
