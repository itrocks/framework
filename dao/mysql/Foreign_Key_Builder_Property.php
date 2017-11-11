<?php
namespace ITRocks\Framework\Dao\Mysql;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Reflection\Annotation\Property\Link_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Store_Name_Annotation;
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
	private static function propertyConstraintToMysql($table_name, Reflection_Property $property)
	{
		return substr(
			$table_name . DOT . (
				Link_Annotation::of($property)->value
					? ('id_' . Store_Name_Annotation::of($property)->value)
					: Store_Name_Annotation::of($property)->value
			),
			0,
			64
		);
	}

	//------------------------------------------------------------------------- propertyFieldsToMysql
	/**
	 * @param $property Reflection_Property
	 * @return string
	 */
	private static function propertyFieldsToMysql(Reflection_Property $property)
	{
		return 'id_' . Store_Name_Annotation::of($property)->value;
	}

	//----------------------------------------------------------------------- propertyOnDeleteToMysql
	/**
	 * @param $property Reflection_Property
	 * @return string
	 */
	private static function propertyOnDeleteToMysql(Reflection_Property $property)
	{
		return $property->getAnnotation('composite')->value ? 'CASCADE' : 'RESTRICT';
	}

	//----------------------------------------------------------------------- propertyOnUpdateToMysql
	/**
	 * @return string
	 */
	private static function propertyOnUpdateToMysql()
	{
		return 'RESTRICT';
	}

	//---------------------------------------------------------------- propertyReferenceFieldsToMysql
	/**
	 * @return string
	 */
	private static function propertyReferenceFieldsToMysql()
	{
		return 'id';
	}

	//----------------------------------------------------------------- propertyReferenceTableToMysql
	/**
	 * @param $property Reflection_Property
	 * @return string
	 */
	private static function propertyReferenceTableToMysql(Reflection_Property $property)
	{
		return Dao::storeNameOf($property->getType()->asString());
	}

}
