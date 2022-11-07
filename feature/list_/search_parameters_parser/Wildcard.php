<?php
namespace ITRocks\Framework\Feature\List_\Search_Parameters_Parser;

use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Dao\Func\Comparison;

/**
 * Wildcard search parameters parser
 */
abstract class Wildcard
{

	//-------------------------------------------------------------------------------- applyWildcards
	/**
	 * @param $search_value   string
	 * @param $is_range_value boolean  true if we parse a range value
	 * @return Comparison|string
	 */
	public static function applyWildcards(string $search_value, bool $is_range_value = false)
		: Comparison|string
	{
		// $search = str_replace(['*', '?'], ['%', '_'], $search_value);
		$search = preg_replace(['/[*%]/', '/[?_]/'], ['%', '_'], $search_value, -1, $count);
		if ($count && !$is_range_value) {
			$search = Func::like($search);
		}
		return $search;
	}

	//----------------------------------------------------------------------------- containsWildcards
	/**
	 * Returns true if as string contains wildcards
	 *
	 * Mixed wildcards are accepted :
	 * - '%' or '*' : multiple characters
	 * - '_' or '?' : one character
	 *
	 * @param $string string
	 * @return boolean
	 */
	public static function containsWildcards(string $string) : bool
	{
		return str_contains(strtr($string, '?_%', '***'), '*');
	}

	//----------------------------------------------------------------------------------- hasWildcard
	/**
	 * Check if expression has any wildcard
	 *
	 * @param $search_value string
	 * @return boolean
	 */
	public static function hasWildcard(string $search_value) : bool
	{
		return preg_match('/[*?%_]/', $search_value);
	}

}
