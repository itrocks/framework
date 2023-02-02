<?php
namespace ITRocks\Framework\Feature\List_\Search_Parameters_Parser;

use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Feature\List_\Search_Parameters_Parser;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Annotation\Property\Null_Annotation;
use ITRocks\Framework\Reflection\Attribute\Class_\Extend;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Reflection\Type;
use ITRocks\Framework\Tools\Date_Time;

/**
 * Word search parameters parser
 */
#[Extend(Search_Parameters_Parser::class)]
abstract class Words
{

	//------------------------------------------------------------------------- applyWordMeaningEmpty
	/**
	 * If expression is a word meaning "empty", convert to corresponding dao search condition
	 *
	 * @param $expression string
	 * @param $property   ?Reflection_Property
	 * @return Func\Comparison|Func\Logical|null
	 */
	public static function applyWordMeaningEmpty(string $expression, ?Reflection_Property $property)
		: Func\Comparison|Func\Logical|null
	{
		if (!self::meansEmpty($expression)) {
			return null;
		}
		$type = $property ? $property->getType() : new Type(Type::STRING);
		if ($type->isString() || $type->isMultipleString()) {
			if ($property && ($property->path || Null_Annotation::of($property)->value)) {
				return Func::orOp([Func::equal(''), Func::isNull()]);
			}
			return Func::equal('');
		}
		elseif ($type->isDateTime()) {
			return Func::orOp([Date_Time::min(), Date_Time::max(), Func::isNull()]);
		}
		return Func::isNull();
	}

	//---------------------------------------------------------------------------- getCompressedWords
	/**
	 * Trim words, removes spaces and some chars like apostrophe, then transliterate to remove accents
	 *
	 * @param $words string[]
	 * @return string[]
	 */
	public static function getCompressedWords(array $words) : array
	{
		array_walk($words, function(string &$word) {
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
	public static function meansEmpty(string $word) : bool
	{
		return in_array(
			trim($word),
			['empty', 'none', 'null', Loc::tr('empty'), Loc::tr('none'), Loc::tr('null')]
		);
	}

}
