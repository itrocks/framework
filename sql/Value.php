<?php
namespace ITRocks\Framework\Sql;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Reflection\Attribute\Property\Values;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Tools\String_Class;

/**
 * Sql value tool methods
 */
abstract class Value
{

	//---------------------------------------------------------------------------------------- escape
	/**
	 * Escapes a column value, in order to insert it into a SQL query
	 * Adds quotes around string / escaped values
	 *
	 * @param $value            mixed
	 * @param $double_backquote boolean
	 * @param $property         Reflection_Property|null
	 * @return string
	 */
	public static function escape(
		mixed $value, bool $double_backquote = false, Reflection_Property $property = null
	) : string
	{
		$type = $property?->getType();
		// no is_numeric(), as sql numeric search make numeric conversion of string fields
		// e.g. WHERE NAME = 500 instead of '500' will give you '500' and '500L', which is not correct
		if (
			isStrictNumeric($value)
			&& strval($value)[0]
			&& (!$type || $type->isNumeric() || $type->isClass())
			&& (!$property || !Values::of($property)?->values)
		) {
			$string_value = strval($value);
		}
		elseif (is_bool($value) && (!$type || $type->isBoolean())) {
			$string_value = ($value ? '1' : '0');
		}
		elseif ($value === null) {
			$string_value = 'NULL';
		}
		elseif (is_array($value)) {
			$has_quotes = false;
			$strings    = [];
			foreach ($value as $object_value) {
				if (is_null($object_value)) {
					continue;
				}
				if (str_contains($object_value, ',')) {
					$has_quotes = true;
				}
				$strings[] = Dao::current()->escapeString($object_value);
			}
			$string_value = DQ . join($has_quotes ? LF : ',', $strings) . DQ;
		}
		elseif ($value instanceof Date_Time) {
			$string_value = Q . $value->toISO(false) . Q;
		}
		elseif ($value instanceof String_Class) {
			$string_value = DQ . Dao::current()->escapeString(strval($value)) . DQ;
		}
		elseif (is_object($value)) {
			$value        = Dao::current()->escapeString($value);
			$string_value = is_numeric($value) ? $value : (DQ . $value . DQ);
		}
		else {
			if ((substr($value, 0, 2) === ('X' . Q)) && (substr($value, -1) === Q)) {
				$string_value = 'X' . Q . Dao::current()->escapeString(substr($value, 2, -1)) . Q;
			}
			else {
				$string_value = DQ . Dao::current()->escapeString($value) . DQ;
			}
		}
		return $double_backquote
			? str_replace(BS, BS . BS, str_replace(BS . DQ, DQ . DQ, $string_value))
			: $string_value;
	}

	//---------------------------------------------------------------------------------------- isLike
	/**
	 * Returns true if value represents a 'LIKE' expression
	 *
	 * Checks if value contains non-escaped '%' or '_'.
	 *
	 * @param $value string
	 * @return boolean
	 */
	public static function isLike(string $value) : bool
	{
		return (substr_count($value, '%') > substr_count($value, BS . '%'))
			|| (substr_count($value, '_') > substr_count($value, BS . '_'));
	}

}
