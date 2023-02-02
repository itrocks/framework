<?php
namespace ITRocks\Framework\Dao\Mysql;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Reflection\Annotation\Property\Link_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Store_Name_Annotation;
use ITRocks\Framework\Reflection\Attribute\Property\Composite;
use ITRocks\Framework\Reflection\Reflection_Property;

/**
 * This builds a mysql foreign key associated to a class property
 */
trait Foreign_Key_Builder_Property
{

	//--------------------------------------------------------------------- propertyConstraintToMysql
	/**
	 * @param $table_name string
	 * @param $property   Reflection_Property
	 * @return string
	 */
	private static function propertyConstraintToMysql(
		string $table_name, Reflection_Property $property
	) : string
	{
		$column_name = Link_Annotation::of($property)->value
			? ('id_' . Store_Name_Annotation::of($property)->value)
			: Store_Name_Annotation::of($property)->value;

		$constraint = $table_name . DOT . $column_name;
		if (strlen($constraint) > 64) {
			$constraint = md5($table_name) . md5($column_name);
		}
		return $constraint;
	}

	//------------------------------------------------------------------------- propertyFieldsToMysql
	/**
	 * @param $property Reflection_Property
	 * @return string
	 */
	private static function propertyFieldsToMysql(Reflection_Property $property) : string
	{
		return 'id_' . Store_Name_Annotation::of($property)->value;
	}

	//----------------------------------------------------------------------- propertyOnDeleteToMysql
	/**
	 * @param $property Reflection_Property
	 * @return string
	 */
	private static function propertyOnDeleteToMysql(Reflection_Property $property) : string
	{
		$constraint = $property->getAnnotation('delete_constraint')->value;
		if ($constraint && ($constraint !== 'initial')) {
			return strtoupper(str_replace('_', SP, $constraint));
		}
		if ($property->getAnnotation('constraint')->value === 'set_null') {
			return Foreign_Key::SET_NULL;
		}
		return (Composite::of($property)?->value || $property->getAnnotation('link_composite')->value)
			? Foreign_Key::CASCADE
			: Foreign_Key::RESTRICT;
	}

	//----------------------------------------------------------------------- propertyOnUpdateToMysql
	/**
	 * @param $property Reflection_Property
	 * @return string
	 */
	private static function propertyOnUpdateToMysql(Reflection_Property $property) : string
	{
		$constraint = $property->getAnnotation('update_constraint')->value;
		if ($constraint && ($constraint !== 'initial')) {
			return strtoupper(str_replace('_', SP, $constraint));
		}
		if ($property->getAnnotation('constraint')->value === 'set_null') {
			return Foreign_Key::CASCADE;
		}
		return (Composite::of($property)?->value || $property->getAnnotation('link_composite')->value)
			? Foreign_Key::CASCADE
			: Foreign_Key::RESTRICT;
	}

	//---------------------------------------------------------------- propertyReferenceFieldsToMysql
	/**
	 * @return string
	 */
	private static function propertyReferenceFieldsToMysql() : string
	{
		return 'id';
	}

	//----------------------------------------------------------------- propertyReferenceTableToMysql
	/**
	 * @param $property Reflection_Property
	 * @return string
	 */
	private static function propertyReferenceTableToMysql(Reflection_Property $property) : string
	{
		return Dao::storeNameOf($property->getType()->asString());
	}

}
