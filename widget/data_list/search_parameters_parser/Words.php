<?php
namespace ITRocks\Framework\Widget\Data_List\Search_Parameters_Parser;

use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Locale\Loc;

/**
 * Word search parameters parser
 *
 * @extends Search_Parameter_Parser
 */
abstract class Words
{

	//-------------------------------------------------------------------------------- applyEmptyWord
	/**
	 * If expression is a date empty word, convert to corresponding value
	 *
	 * @param $expression string
	 * @return mixed|boolean false
	 */
	public static function applyEmptyWord($expression)
	{
		if (self::isEmptyWord($expression)) {
			return Func::isNull();
		}
		// not an empty word
		return false;
	}

	//---------------------------------------------------------------------------- getCompressedWords
	/**
	 * Trim words, removes spaces and some chars like apostrophe, then transliterate to remove accents
	 *
	 * @param $words string[]
	 * @return string[]
	 */
	public static function getCompressedWords(array $words)
	{
		array_walk($words, function(&$word) {
			// TODO iconv with //TRANSLIT requires that locale is different than C or Posix. To Do: a better support!!
			// See: http://php.net/manual/en/function.iconv.php#74101
			$word = preg_replace('/\s|\'/', '', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $word)));
		});
		return $words;
	}

	//----------------------------------------------------------------------------------- isEmptyWord
	/**
	 * Check if expression is an empty word
	 *
	 * @param $word string
	 * @return boolean true if empty word
	 */
	private static function isEmptyWord($word)
	{
		if (!Wildcard::containsWildCards($word)) {
			$word = Loc::rtr($word);
		}
		// TODO iconv with //TRANSLIT requires that locale is different than C or Posix. To Do: a better support !!
		// See: http://php.net/manual/en/function.iconv.php#74101
		$word = preg_replace('/\s|\'/', '', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $word)));
		return in_array($word, ['empty', 'none', 'null']);
	}

}
