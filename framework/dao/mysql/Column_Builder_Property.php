<?php
namespace SAF\Framework\Dao\Mysql;

use DateTime;
use SAF\Framework\Reflection\Reflection_Property;
use SAF\Framework\Reflection\Type;
use SAF\Framework\Tools\Date_Time;

/**
 * This builds a mysql column associated to a class property
 */
trait Column_Builder_Property
{

	//------------------------------------------------------------------------- propertyTypeToDefault
	/**
	 * Gets mysql default value for a property
	 *
	 * Must be called only after $column's Null and Type has been set
	 *
	 * @param $property Reflection_Property
	 * @param $column Column
	 * @return mixed
	 */
	private static function propertyDefaultToMysql(
		Reflection_Property $property, Column $column
	) {
		$default = $property->getDeclaringClass()->getDefaultProperties()[$property->name];
		if (isset($default)) {
			$property_type = $column->getType();
			if ($property_type->isNumeric()) {
				$default = $default + 0;
			}
		}
		else {
			if ($column->canBeNull()) {
				$default = null;
			}
			else {
				$property_type = $column->getType();
				if ($property_type->isNumeric()) {
					$default = 0;
				}
				elseif ($property_type->isString() || $property_type->isMultipleString()) {
					$default = '';
				}
				elseif ($property_type->isDateTime()) {
					$default = '0000-00-00 00:00:00';
				}
			}
		}
		return $default;
	}

	//---------------------------------------------------------------------------- propertyKeyToMysql
	/**
	 * @param $property Reflection_Property
	 * @return string
	 */
	private static function propertyKeyToMysql(
		/** @noinspection PhpUnusedParameterInspection */
		Reflection_Property $property
	) {
		// TODO automatic keys on object linked tables
		return '';
	}

	//--------------------------------------------------------------------------- propertyNameToMysql
	/**
	 * Gets the mysql field name for a property
	 *
	 * @param $property Reflection_Property
	 * @return string
	 */
	private static function propertyNameToMysql(Reflection_Property $property)
	{
		$type = $property->getType();
		return ($type->isBasic() || ($type->isMultiple() && $type->getElementType()->isString()))
			? $property->getAnnotation('storage')->value
			: ('id_' . $property->getAnnotation('storage')->value);
	}

	//--------------------------------------------------------------------------- propertyNullToMysql
	/**
	 * Gets mysql expression for a property that can be null (or not)
	 *
	 * @param $property Reflection_Property
	 * @return string
	 */
	private static function propertyNullToMysql(Reflection_Property $property)
	{
		return $property->getAnnotation('null')->value ? 'YES' : 'NO';
	}

	//--------------------------------------------------------------------------- propertyTypeToMysql
	/**
	 * Gets mysql expression for a property type
	 *
	 * @param $property Reflection_Property
	 * @return string
	 */
	private static function propertyTypeToMysql(Reflection_Property $property)
	{
		$property_type = $property->getType();
		if ($property_type->isBasic()) {
			if ($property_type->hasSize()) {
				/** @var integer $max_length */
				$max_length = $property->getAnnotation('max_length')->value;
				$max_value  = $property->getAnnotation('max_value')->value;
				$min_value  = $property->getAnnotation('min_value')->value;
				$precision  = $property->getAnnotation('precision')->value;
				$signed     = $property->getAnnotation('signed')->value;
				$signed_length = $max_length + ($signed ? 1 : 0);
				if (!isset($max_length)) {
					if ($property_type->isNumeric()) {
						$max_length = 18;
						$signed_length = 18;
					}
					else {
						$max_length = 255;
					}
				}
				if ($property_type->isInteger()) {
					return ($max_length <= 3  && $min_value >= -128 && $max_value <= 127 && $signed) ? 'tinyint(' . $signed_length . ')' : (
						($max_length <= 3 && $min_value >= 0 && $max_value <= 255 && !$signed) ? 'tinyint(' . $max_length . ') unsigned' : (
						($max_length <= 5 && $min_value >= -32768 && $max_value <= 32767) ? 'smallint(' . $signed_length . ')' : (
						($max_length <= 5 && $min_value >= 0 && $max_value <= 65535) ? 'smallint(' . $max_length . ') unsigned' : (
						($max_length <= 7 && $min_value >= -8388608 && $max_value <= 8388607) ? 'mediumint(' . $signed_length . ')' : (
						($max_length <= 8 && $min_value >= 0 && $max_value <= 16777215) ? 'mediumint(' . $max_length . ') unsigned' : (
						($max_length <= 10 && $min_value >= -2147483648 && $max_value <= 2147483647) ? 'int(' . $signed_length . ')' : (
						($max_length <= 10 && $min_value >= 0 && $max_value <= 4294967295) ? 'int(' . $max_length . ') unsigned' : (
						($max_length <= 19 && $min_value >= -9223372036854775808 && $max_value <= 9223372036854775806) ? 'bigint(' . $signed_length . ')' : (
						'bigint(' . $max_length . ') unsigned'
					)))))))));
				}
				elseif ($property_type->isFloat()) {
					return ($precision ? 'decimal(' . $signed_length . ', ' . $precision . ')' : 'double');
				}
				elseif ($property->getAnnotation('binary')->value) {
					return ($max_length <= 65535) ? 'blob' : (
						($max_length <= 16777215) ? 'mediumblob' :
						'longblob'
					);
				}
				else {
					return ($max_length <= 3) ? 'char(' . $max_length . ')' : (
						($max_length <= 255) ? 'varchar(' . $max_length . ')' : (
						($max_length <= 65535) ? 'text' : (
						($max_length <= 16777215) ? 'mediumtext' :
						'longtext'
					))) . ' CHARACTER SET utf8 COLLATE utf8_general_ci';
				}
			}
			switch ($property_type->asString()) {
				case Type::_ARRAY:
					return null;
				case Type::BOOLEAN:
					return 'tinyint(1)';
				case Type::_CALLABLE:
					return null;
				case Type::null: case Type::NULL:
					return null;
				case Type::RESOURCE:
					return null;
				case DateTime::class: case Date_Time::class:
					return 'datetime';
				default:
					return 'char(255)';
			}
		}
		elseif ($property_type->asString() === Type::STRING_ARRAY) {
			/** @var $values string[] */
			$values = [];
			foreach ($property->getListAnnotation('values')->values() as $key => $value) {
				$values[$key] = str_replace(Q, Q . Q, $value);
			}
			return $values
				? (
					($property->getAnnotation('set')->value ? 'set' : 'enum')
					. "('" . join("','", $values) . "')"
				)
				: 'char(255)';
		}
		else {
			return 'bigint(18) unsigned';
		}
	}

}
