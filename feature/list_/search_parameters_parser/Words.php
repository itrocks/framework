<?php
namespace ITRocks\Framework\Feature\List_\Search_Parameters_Parser;

use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Annotation\Property\Null_Annotation;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Tools\Date_Time;

/**
 * Word search parameters parser
 *
 * @extends Search_Parameter_Parser
 */
abstract class Words
{

	//------------------------------------------------------------------------- applyWordMeaningEmpty
	/**
	 * If expression is a word meaning "empty", convert to corresponding dao search condition
	 *
	 * @param $expression string
	 * @param $property   Reflection_Property
	 * @return Func\Where|null
	 */
	public static function applyWordMeaningEmpty($expression, Reflection_Property $property)
	{
		if (self::meansEmpty($expression)) {
			$type = $property->getType();
			if ($type->isString() || $type->isMultipleString()) {
				if (Null_Annotation::of($property)->value) {
					return Func::orOp([Func::isNull(), Func::equal('')]);
				}
				return Func::equal('');
			}
			elseif ($type->isDateTime()) {
				return Func::orOp([Date_Time::min(), Date_Time::max()]);
			}
			else {
				return Func::isNull();
			}
		}
		return null;
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

	//------------------------------------------------------------------------------------ meansEmpty
	/**
	 * Check if expression is an empty word
	 *
	 * @param $word string
	 * @return boolean true if empty word
	 */
	public static function meansEmpty($word)
	{
		if (!Wildcard::containsWildcards($word)) {
			$word = Loc::rtr($word);
		}
		// TODO iconv with //TRANSLIT requires that locale is different than C or Posix. To Do: a better support !!
		// See: http://php.net/manual/en/function.iconv.php#74101
		$word = preg_replace('/\s|\'/', '', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $word)));
		return in_array($word, ['empty', 'none', 'null']);
	}

}
