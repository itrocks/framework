<?php
namespace ITRocks\Framework\Dao\Mysql;

use DateTime;
use ITRocks\Framework\Dao\Mysql\Column_Builder_Property\Decimal;
use ITRocks\Framework\Dao\Mysql\Column_Builder_Property\Integer;
use ITRocks\Framework\Reflection\Annotation\Property\Store_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Store_Name_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Values_Annotation;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Reflection\Type;
use ITRocks\Framework\Tools\Date_Time;

/**
 * This builds a mysql column associated to a class property
 */
trait Column_Builder_Property
{

	//------------------------------------------------------------------------ propertyDefaultToMysql
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
		$default = $property->getDefaultValue(false);
		if (isset($default)) {
			$property_type = $column->getType();
			if ($property_type->isInteger()) {
				$default = intval($default);
			}
			elseif ($property_type->isFloat()) {
				$default = floatval($default);
			}
			elseif (
				($default === '')
				&& $column->alwaysNullDefault()
				&& ($property_type->isString() || $property_type->isMultipleString())
			) {
				$default = null;
			}
			elseif (is_array($default)) {
				if ($default) {
					$default = join(',', $default);
				}
				else {
					// if @values, then set with a real value
					// if no @values, the string [] is stored as as text : null default value even if not null
					$default = Values_Annotation::of($property)->value ? '' : null;
				}
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
				elseif (
					($property_type->isString() || $property_type->isMultipleString())
					&& !$column->alwaysNullDefault()
				) {
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
		return ($type->isBasic() || Store_Annotation::of($property)->value)
			? Store_Name_Annotation::of($property)->value
			: ('id_' . Store_Name_Annotation::of($property)->value);
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
		$property_type          = $property->getType();
		$store_annotation_value = Store_Annotation::of($property)->value;
		if ($property_type->isBasic() || $store_annotation_value) {
			if ($property_type->isMultipleString()) {
				$values = self::propertyValues($property);
				return ($values ? 'set(' . Q . join(Q . ',' . Q, $values) . Q . ')' : 'text')
					. SP . Database::characterSetCollateSql();
			}
			if ($property_type->hasSize()) {
				$max_length = $property->getAnnotation('max_length')->value;
				$max_value  = $property->getAnnotation('max_value')->value;
				$min_value  = $property->getAnnotation('min_value')->value;
				$precision  = $property->getAnnotation('precision')->value;
				$signed     = $property->getAnnotation('signed')->value;
				if ($property_type->isInteger()) {
					return (new Integer)->type($max_length, $min_value, $max_value, $signed);
				}
				elseif ($property_type->isFloat()) {
					return $precision
						? (new Decimal)->type($max_length, $min_value, $max_value, $signed, $precision)
						: 'double';
				}
				elseif ($property->getAnnotation('binary')->value) {
					return (isset($max_length) && ($max_length <= 255)) ? 'tinyblob' : (
						(isset($max_length) && ($max_length <= 65535)) ? 'blob' : (
						(isset($max_length) && ($max_length <= 16777215)) ? 'mediumblob' :
						'longblob'
					));
				}
				else {
					$values = self::propertyValues($property);
					if ($values && !$store_annotation_value) {
						if (!isset($values[''])) {
							$values[''] = '';
						}
						return 'enum(' . Q . join(Q . ',' . Q, $values) . Q . ')'
							. SP . Database::characterSetCollateSql();
					}
					if (!isset($max_length)) {
						$max_length = 255;
					}
					if ($store_annotation_value === Store_Annotation::GZ) {
						return ($max_length <= 255) ? 'tinyblob' : (
							($max_length <= 65535)    ? 'blob' : (
							($max_length <= 16777215) ? 'mediumblob' :
							'longblob'
						));
					}
					return static::sqlTextColumn($max_length);
				}
			}
			elseif (
				in_array($store_annotation_value, [Store_Annotation::JSON, Store_Annotation::STRING])
				&& !$property_type->isDateTime()
			) {
				return static::sqlTextColumn($property->getAnnotation('max_length')->value ?: 255);
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
					return static::sqlTextColumn($property->getAnnotation('max_length')->value ?: 255);
			}
		}
		else {
			return 'bigint(18) unsigned';
		}
	}

	//-------------------------------------------------------------------------------- propertyValues
	/**
	 * @param $property Reflection_Property
	 * @return string[]
	 */
	private static function propertyValues(Reflection_Property $property)
	{
		$values = $property->getListAnnotation('values')->values();
		if ($values) {
			foreach ($values as $key => $value) {
				$values[$key] = str_replace(Q, Q . Q, $value);
			}
			return $values;
		}
		else {
			return [];
		}
	}

	//--------------------------------------------------------------------------------- sqlTextColumn
	/**
	 * @param $max_length \integer
	 * @return string
	 */
	private static function sqlTextColumn($max_length)
	{
		return (
			($max_length <= 3)        ? ('char('    . $max_length . ')') : (
			($max_length <= 255)      ? ('varchar(' . $max_length . ')') : (
			($max_length <= 65535)    ? 'text' : (
			($max_length <= 16777215) ? 'mediumtext' :
				'longtext'
			)))
			) . SP . Database::characterSetCollateSql();
	}

}
