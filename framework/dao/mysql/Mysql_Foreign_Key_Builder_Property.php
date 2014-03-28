<?php
namespace SAF\Framework;

/**
 * This builds a mysql foreign key associated to a class property
 */
trait Mysql_Foreign_Key_Builder_Property
{

	//--------------------------------------------------------------------- propertyConstraintToMysql
	/**
	 * @param $table_name string
	 * @param $property   Reflection_Property
	 * @return string
	 */
	private static function propertyConstraintToMysql($table_name, Reflection_Property $property)
	{
		return $table_name . DOT
		. (
			$property->getAnnotation('link')->value
			? ('id_' . $property->getAnnotation('storage')->value)
			: $property->getAnnotation('storage')->value
		);
	}

	//------------------------------------------------------------------------- propertyFieldsToMysql
	/**
	 * @param $property Reflection_Property
	 * @return string
	 */
	private static function propertyFieldsToMysql(Reflection_Property $property)
	{
		return 'id_' . $property->getAnnotation('storage')->value;
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

	//---------------------------------------------------------------- propertyReferenceFieldsToMysql
	/**
	 * @return string
	 */
	private static function propertyReferenceFieldsToMysql()
	{
		return 'id';
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

}
