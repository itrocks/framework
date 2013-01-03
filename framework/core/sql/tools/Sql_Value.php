<?php
namespace SAF\Framework;

abstract class Sql_Value
{

	//---------------------------------------------------------------------------------------- escape
	/**
	 * Escape a column value, in order to insert it into a SQL query
	 *
	 * @param mixed $value
	 * @return string
	 */
	public static function escape($value)
	{
		if (is_numeric($value)) {
			$string_value = "$value";
		}
		elseif (is_bool($value)) {
			$string_value = ($value ? "1" : "0");
		}
		elseif ($value === null) {
			$string_value = "NULL";
		}
		elseif (is_array($value)) {
			$do = false;
			$string_value = "";
			foreach ($value as $object_value) {
				if ($object_value !== null) {
					if ($do) $string_value .= ",";
					$string_value .= str_replace("'", "''", $object_value); 
					$do = true;
				}
			}
			$string_value = substr($string_value, 2);
		}
		elseif ($value instanceof Date_Time) {
			$string_value = "\"" . $value->toISO() . "\"";
		}
		else {
			$string_value = "\"" . str_replace(
				array("\"", "\\"), array("\"\"", "\\\\"), $value)
			. "\"";
		}
		return $string_value;
	}

	//---------------------------------------------------------------------------------------- isLike
	/**
	 * Returns true if value represents a "LIKE" expression
	 *
	 * Checks if value contains non-escaped "%" or "_".
	 *
	 * @param mixed $value
	 * @return string
	 */
	public static function isLike($value)
	{
		return (substr_count($value, "%") > substr_count($value, "\\%"))
			|| (substr_count($value, "_") > substr_count($value, "\\_"));
	}

}
