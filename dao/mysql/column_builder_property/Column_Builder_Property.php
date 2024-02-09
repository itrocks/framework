<?php
namespace ITRocks\Framework\Dao\Mysql;

use DateTime;
use ITRocks\Framework\Dao\Mysql\Column_Builder_Property\Decimal;
use ITRocks\Framework\Dao\Mysql\Column_Builder_Property\Integer;
use ITRocks\Framework\Feature\Validate\Property\Max_Length;
use ITRocks\Framework\Feature\Validate\Property\Max_Value;
use ITRocks\Framework\Feature\Validate\Property\Min_Value;
use ITRocks\Framework\Reflection\Annotation\Property\Null_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Store_Name_Annotation;
use ITRocks\Framework\Reflection\Attribute\Property\Decimals;
use ITRocks\Framework\Reflection\Attribute\Property\Default_;
use ITRocks\Framework\Reflection\Attribute\Property\Store;
use ITRocks\Framework\Reflection\Attribute\Property\Values;
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
	 * Must be called only after $column's Null and Type has been set
	 */
	private static function propertyDefaultToMysql(
		Reflection_Property $property, Column $column
	) : mixed
	{
		$default       = $property->getDefaultValue('constant');
		$property_type = $property->getType();
		if ($property_type->isDateTime() && Default_::of($property)?->is([Date_Time::class, 'now'])) {
			$default = 'CURRENT_TIMESTAMP';
		}
		if (isset($default)) {
			if ($property_type->isInteger()) {
				$default = is_object($default)
					? ($default->id ?? 0)
					: intval($default);
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
					// if #Values, then set with a real value
					// if no #Values, the string [] is stored as text : null default value even if not null
					$default = Values::of($property)?->values ? '' : null;
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
	private static function propertyKeyToMysql(
		/** @noinspection PhpUnusedParameterInspection */
		Reflection_Property $property
	) : string
	{
		// TODO automatic keys on object linked tables
		return '';
	}

	//--------------------------------------------------------------------------- propertyNameToMysql
	/** Gets the mysql field name for a property */
	private static function propertyNameToMysql(Reflection_Property $property) : string
	{
		$type = $property->getType();
		return ($type->isBasic() || Store::of($property)->isString())
			? Store_Name_Annotation::of($property)->value
			: ('id_' . Store_Name_Annotation::of($property)->value);
	}

	//--------------------------------------------------------------------------- propertyNullToMysql
	/**
	 * Gets mysql expression for a property that can be null (or not)
	 *
	 * @return string @values NO, YES
	 */
	private static function propertyNullToMysql(Reflection_Property $property) : string
	{
		return Null_Annotation::of($property)->value ? 'YES' : 'NO';
	}

	//--------------------------------------------------------------------------- propertyTypeToMysql
	/** Gets mysql expression for a property type */
	private static function propertyTypeToMysql(Reflection_Property $property) : string
	{
		$property_type    = $property->getType();
		$store_annotation = Store::of($property);
		if ($property_type->isBasic() || !$store_annotation->isFalse()) {
			if ($property_type->isMultipleString()) {
				$values = self::propertyValues($property);
				return ($values ? 'set(' . Q . join(Q . ',' . Q, $values) . Q . ')' : 'text')
					. SP . Database::characterSetCollateSql();
			}
			if ($property_type->hasSize()) {
				$decimals   = Decimals::of($property)?->value;
				$max_length = Max_Length::of($property)?->value;
				$max_value  = Max_Value::of($property)?->value;
				$min_value  = Min_Value::of($property)?->value;
				$signed     = $property->getAnnotation('signed')->value;
				if ($property_type->isInteger()) {
					return (new Integer)->type($max_length, $min_value, $max_value, $signed);
				}
				elseif ($property_type->isFloat()) {
					return $decimals
						? (new Decimal)->type($max_length, $min_value, $max_value, $signed, $decimals)
						: 'double';
				}
				elseif ($property->getAnnotation('binary')->value) {
					return static::sqlBlobColumn((int)$max_length);
				}
				else {
					$values = self::propertyValues($property);
					if ($values && !$store_annotation->isFalse()) {
						if (!isset($values[''])) {
							$values[''] = '';
						}
						return 'enum(' . Q . join(Q . ',' . Q, $values) . Q . ')'
							. SP . Database::characterSetCollateSql();
					}
					if (!isset($max_length)) {
						$max_length = 255;
					}
					if ($store_annotation->isGz()) {
						return static::sqlBlobColumn($max_length);
					}
					return static::sqlTextColumn($max_length);
				}
			}
			elseif ($store_annotation->isJson() || $store_annotation->isSerialize()) {
				return static::sqlTextColumn($property->getAnnotation('max_length')->value ?: 65535);
			}
			switch ($property_type->asString()) {
				case Type::_ARRAY:
				case Type::_CALLABLE:
				case Type::null:
				case Type::NULL:
				case Type::RESOURCE:
					return '';
				case Type::BOOLEAN:
				case Type::FALSE:
				case Type::TRUE:
					return 'tinyint(1)';
				case DateTime::class: case Date_Time::class:
					return 'datetime';
				default:
					return static::sqlTextColumn(Max_Length::of($property)?->value ?: 255);
			}
		}
		else {
			return 'bigint(18) unsigned';
		}
	}

	//-------------------------------------------------------------------------------- propertyValues
	/** @return string[] */
	private static function propertyValues(Reflection_Property $property) : array
	{
		$values = Values::of($property)?->values ?: [];
		foreach ($values as $key => $value) {
			$values[$key] = str_replace(Q, Q . Q, $value);
		}
		return $values;
	}

	//--------------------------------------------------------------------------------- sqlBlobColumn
	private static function sqlBlobColumn(int $max_length) : string
	{
		return ($max_length && ($max_length <= 255)) ? 'tinyblob'   : (
			($max_length && ($max_length <= 65535))    ? 'blob'       : (
			($max_length && ($max_length <= 16777215)) ? 'mediumblob' :
			'longblob'
		));
	}

	//--------------------------------------------------------------------------------- sqlTextColumn
	private static function sqlTextColumn(int $max_length) : string
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
