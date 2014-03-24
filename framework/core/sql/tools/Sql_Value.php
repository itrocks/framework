<?php
namespace SAF\Framework;

/**
 * Sql value tool methods
 */
abstract class Sql_Value
{

	//---------------------------------------------------------------------------------------- escape
	/**
	 * Escape a column value, in order to insert it into a SQL query
	 *
	 * @param $value            mixed
	 * @param $double_backquote boolean
	 * @return string
	 */
	public static function escape($value, $double_backquote = false)
	{
		// no is_numeric(), as sql numeric search make numeric conversion of string fields
		// ie WHERE NAME = 500 instead of '500' will give you '500' and '500L', which is not correct
		if (is_integer($value) || is_float($value)) {
			$string_value = strval($value);
		}
		elseif (is_bool($value)) {
			$string_value = ($value ? '1' : '0');
		}
		elseif ($value === null) {
			$string_value = 'NULL';
		}
		elseif (is_array($value)) {
			$do = false;
			$string_value = '';
			foreach ($value as $object_value) {
				if ($object_value !== null) {
					if ($do) $string_value .= ',';
					$string_value .= str_replace(DQ, DQ . DQ, $object_value);
					$do = true;
				}
			}
			$string_value = substr($string_value, 2);
		}
		elseif ($value instanceof Date_Time) {
			$string_value = DQ . $value->toISO() . DQ;
		}
		else {
			$string_value = DQ . Dao::current()->escapeString($value) . DQ;
		}
		return $double_backquote ? str_replace(BS, BS . BS, $string_value) : $string_value;
	}

	//---------------------------------------------------------------------------------------- isLike
	/**
	 * Returns true if value represents a 'LIKE' expression
	 *
	 * Checks if value contains non-escaped '%' or '_'.
	 *
	 * @param $value mixed
	 * @return string
	 */
	public static function isLike($value)
	{
		return (substr_count($value, '%') > substr_count($value, BS . '%'))
			|| (substr_count($value, '_') > substr_count($value, BS . '_'));
	}

}
