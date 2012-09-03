<?php
namespace Framework;

class Sql_Value
{

	//---------------------------------------------------------------------------------------- escape
	public static function escape($value)
	{
		if ($value === null) {
			$string_value = "NULL";
		} elseif (is_bool($value)) {
			$string_value = ($value ? "1" : "0");
		} elseif ($value instanceof Date_Time) {
			$string_value = "\"" . $value->toISO() . "\"";
		} elseif (is_numeric($value)) {
			$string_value = "$value";
		} elseif (is_array($value)) {
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
		} else {
			$string_value = "'" . str_replace("'", "''", $value) . "'";
		}
		return $string_value;
	}

}
