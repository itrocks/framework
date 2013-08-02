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
		if (is_numeric($value) && ($value[0] !== '0')) {
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
			if (substr($value, 0, 1) === "\x07") {
				$string_value = "0x";
				$length = strlen($value);
				for ($i = 1; $i < $length; $i ++) {
					$hex = dechex(ord($value[$i]));
					$string_value .= ((strlen($hex) < 2) ? ("0" . $hex) : $hex);

				}
			}
			else {
				$string_value = "\"" . str_replace(
					array("\"", "\\"), array("\"\"", "\\\\"), $value)
				. "\"";
			}
		}
		return $double_backquote ? str_replace("\\", "\\\\", $string_value) : $string_value;
	}

	//---------------------------------------------------------------------------------------- isLike
	/**
	 * Returns true if value represents a "LIKE" expression
	 *
	 * Checks if value contains non-escaped "%" or "_".
	 *
	 * @param $value mixed
	 * @return string
	 */
	public static function isLike($value)
	{
		return (substr_count($value, "%") > substr_count($value, "\\%"))
			|| (substr_count($value, "_") > substr_count($value, "\\_"));
	}

}
