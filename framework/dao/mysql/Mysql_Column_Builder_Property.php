<?php
namespace SAF\Framework;

abstract class Mysql_Column_Builder_Property
{

	//------------------------------------------------------------------------------------- $property
	public static function build(Reflection_Property $property)
	{
		$column = new Mysql_Column();
		$mysql_column_class->Field = $property->name;
		$type = $property->getType();
		$mysql_column_class->Type = self::propertyTypeToMysqlType($property);
		return $column;
	}

	//----------------------------------------------------------------------- propertyTypeToMysqlType
	private static function propertyTypeToMysqlType(Reflection_Property $property)
	{
		$property_type = $property->getType();
		if (Type::isBasic($property_type)) {
			if (Type::hasSize($property_type)) {
				$max_length = $property->getAnnotation("max_length")->value;
				$max_value  = $property->getAnnotation("max_value")->value;
				$min_value  = $property->getAnnotation("min_value")->value;
				$precision  = $property->getAnnotation("precision")->value;
				$signed     = $property->getAnnotation("signed")->value;
				$signed_length = $max_length + ($signed ? 1 : 0);
			}
			switch ($property_type) {
				case "array":
					return null;
				case "boolean":
					return "TINYINT(1)";
				case "callable":
					return null;
				case "integer":
					return ($max_length <= 3 && $min_value >= -128 && $max_value <= 127 && $signed) ? "TINYINT($signed_length)" : (
						($max_length <= 3 && $min_value >= 0 && $max_value <= 255 && !$signed) ? "TINYINT($max_length)" : (
						($max_length <= 5 && $min_value >= -32768 && $max_value <= 32767) ? "SMALLINT($signed_length)" : (
						($max_length <= 5 && $min_value >= 0 && $max_value <= 65535) ? "SMALLINT($max_length)" : (
						($max_length <= 7 && $min_value >= -8388608 && $max_value <= 8388607) ? "MEDIUMINT($signed_length)" : (
						($max_length <= 8 && $min_value >= 0 && $max_value <= 16777215) ? "MEDIUMINT($max_length)" : (
						($max_length <= 10 && $min_value >= -2147483648 && $max_value <= 2147483647) ? "INT($signed_length)" : (
						($max_length <= 10 && $min_value >= 0 && $max_value <= 4294967295) ? "INT($max_length)" : (
						($max_length <= 19 && $min_value >= -9223372036854775808 && $max_value <= 9223372036854775806) ? "BIGINT($signed_length)" : (
						"BIGINT($max_length)"
					)))))))));
				case "float":
					return ($precision ? "DECIMAL($signed_length, $precision)" : "FLOAT");
				case "null": case "NULL":
					return null;
				case "resource":
					return null;
				case "string":
					return ($max_length <= 3) ? "CHAR($max_length)" : (
						($max_length <= 255) ? "VARCHAR($max_length)" : (
						($max_length <= 65535) ? "TEXT" : (
						($max_length <= 16777215) ? "MEDIUMTEXT" : 
						"LONGTEXT"
					)));
				case "Date_Time":
					return "DATETIME";
			}
		}
		else {
			
		}
	}

}
