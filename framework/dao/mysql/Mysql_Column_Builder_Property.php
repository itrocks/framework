<?php
namespace SAF\Framework;

abstract class Mysql_Column_Builder_Property
{

	//------------------------------------------------------------------------------------- $property
	/**
	 * Builds a Mysql_Column object using a class property
	 *
	 * @param Reflection_Property $property
	 * @return Mysql_Column
	 */
	public static function build(Reflection_Property $property)
	{
		$column = new Mysql_Column();
		$class = Reflection_Class::getInstanceOf(get_class($column));
		$class->accessProperties();
		$class->getProperty("Field")->setValue($column, self::propertyNameToMysql($property));
		$class->getProperty("Type")->setValue($column, self::propertyTypeToMysql($property));
		$class->getProperty("Null")->setValue($column, self::propertyNullToMysql($property));
		$class->getProperty("Default")->setValue($column, self::propertyDefaultToMysql($property, $column));
		$class->getProperty("Extra")->setValue($column, "");
		$class->accessPropertiesDone();
		return $column;
	}

	//--------------------------------------------------------------------------------------- buildId
	/**
	 * Builds a Mysql_Column object for a standard "id" column
	 *
	 * @return Mysql_Column
	 */
	public static function buildId()
	{
		$column = new Mysql_Column();
		$class = Reflection_Class::getInstanceOf(get_class($column));
		$class->accessProperties();
		$class->getProperty("Field")->setValue($column, "id");
		$class->getProperty("Type")->setValue($column, "bigint(18) unsigned");
		$class->getProperty("Null")->setValue($column, "NO");
		$class->getProperty("Default")->setValue($column, null);
		$class->getProperty("Extra")->setValue($column, "auto_increment");
		$class->accessPropertiesDone();
		return $column;
	}

	//------------------------------------------------------------------------- propertyTypeToDefault
	/**
	 * Gets mysql default value for a property
	 *
	 * Must be called only after $column's Null and Type has been set 
	 *
	 * @param Reflection_Property $property
	 * @param Mysql_Column $column
	 * @return mixed
	 */
	private static function propertyDefaultToMysql(
		Reflection_Property $property, Mysql_Column $column
	) {
		$default = $property->getDeclaringClass()->getDefaultProperties()[$property->name];
		if (isset($default)) {
			$property_type = $column->getType();
			if (Type::isNumeric($property_type)) {
				$default = strval($default + 0);
			}
		}
		else {
			if ($column->canBeNull()) {
				$default = null;
			}
			else {
				$property_type = $column->getType();
				if (Type::isNumeric($property_type)) {
					$default = 0;
				}
				elseif ($property_type === "string") {
					$default = "";
				}
				elseif ($property_type === "Date_Time") {
					$default = "0000-00-00 00:00:00";
				}
			}
		}
		return $default;
	}

	//--------------------------------------------------------------------------- propertyNameToMysql
	/**
	 * Gets the mysql field name for a property
	 *
	 * @param Reflection_Property $property
	 * @return string
	 */
	private static function propertyNameToMysql(Reflection_Property $property)
	{
		return Type::isBasic($property->getType())
			? $property->name
			: "id_" . $property->name;
	}

	//--------------------------------------------------------------------------- propertyNullToMysql
	/**
	 * Gets mysql expression for a property that can be null (or not)
	 *
	 * @param Reflection_Property $property
	 * @return string
	 */
	private static function propertyNullToMysql(Reflection_Property $property)
	{
		return $property->getAnnotation("null")->value ? "YES" : "NO";
	}

	//--------------------------------------------------------------------------- propertyTypeToMysql
	/**
	 * Gets mysql expression for a property type
	 *
	 * @param Reflection_Property $property
	 * @return string
	 */
	private static function propertyTypeToMysql(Reflection_Property $property)
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
				if (!isset($max_length)) {
					$max_length = 255;
				}
			}
			switch ($property_type) {
				case "array":
					return null;
				case "boolean":
					return "TINYINT(1)";
				case "callable":
					return null;
				case "integer":
					return ($max_length <= 3 && $min_value >= -128 && $max_value <= 127 && $signed) ? "tinyint($signed_length)" : (
						($max_length <= 3 && $min_value >= 0 && $max_value <= 255 && !$signed) ? "tinyint($max_length) unsigned" : (
						($max_length <= 5 && $min_value >= -32768 && $max_value <= 32767) ? "smallint($signed_length)" : (
						($max_length <= 5 && $min_value >= 0 && $max_value <= 65535) ? "smallint($max_length) unsigned" : (
						($max_length <= 7 && $min_value >= -8388608 && $max_value <= 8388607) ? "mediumint($signed_length)" : (
						($max_length <= 8 && $min_value >= 0 && $max_value <= 16777215) ? "mediumint($max_length) unsigned" : (
						($max_length <= 10 && $min_value >= -2147483648 && $max_value <= 2147483647) ? "int($signed_length)" : (
						($max_length <= 10 && $min_value >= 0 && $max_value <= 4294967295) ? "int($max_length) unsigned" : (
						($max_length <= 19 && $min_value >= -9223372036854775808 && $max_value <= 9223372036854775806) ? "bigint($signed_length)" : (
						"bigint($max_length) unsigned"
					)))))))));
				case "float":
					return ($precision ? "decimal($signed_length, $precision)" : "float");
				case "null": case "NULL":
					return null;
				case "resource":
					return null;
				case "string":
					return ($max_length <= 3) ? "char($max_length)" : (
						($max_length <= 255) ? "varchar($max_length)" : (
						($max_length <= 65535) ? "text" : (
						($max_length <= 16777215) ? "mediumtext" : 
						"longtext"
					)));
				case "Date_Time":
					return "datetime";
			}
		}
		else {
			return "bigint(18) unsigned";
		}
	}

}
