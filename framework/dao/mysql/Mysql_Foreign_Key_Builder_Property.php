<?php
namespace SAF\Framework;

/**
 * This builds a mysql foreign key associated to a class property
 */
abstract class Mysql_Foreign_Key_Builder_Property
{

	//------------------------------------------------------------------------------------- $property
	/**
	 * Builds a Mysql_Column object using a class property
	 *
	 * @param $property Reflection_Property
	 * @return Mysql_Foreign_Key
	 */
	public static function build(Reflection_Property $property)
	{
		$foreign_key = new Mysql_Foreign_Key();
		$class = Reflection_Class::getInstanceOf(get_class($foreign_key));
		$class->accessProperties();
		$class->getProperty("Constraint")->setValue(
			$foreign_key, self::propertyConstraintToMysql($property)
		);
		$class->getProperty("Fields")->setValue(
			$foreign_key, self::propertyFieldsToMysql($property)
		);
		$class->getProperty("On_delete")->setValue(
			$foreign_key, self::propertyOnDeleteToMysql($property)
		);
		$class->getProperty("On_update")->setValue(
			$foreign_key, self::propertyOnUpdateToMysql($property)
		);
		$class->getProperty("Reference_fields")->setValue(
			$foreign_key, self::propertyReferenceFieldsToMysql($property)
		);
		$class->getProperty("Reference_table")->setValue(
			$foreign_key, self::propertyReferenceTableToMysql($property)
		);
		$class->accessPropertiesDone();
		return $foreign_key;
	}

	//--------------------------------------------------------------------- propertyConstraintToMysql
	/**
	 * @param $property Reflection_Property
	 * @return string
	 */
	private static function propertyConstraintToMysql(Reflection_Property $property)
	{
		return Dao::storeNameOf($property->class) . "."
		. ($property->getAnnotation("link")->value ? ("id_" . $property->name) : $property->name);
	}

	//------------------------------------------------------------------------- propertyFieldsToMysql
	/**
	 * @param $property Reflection_Property
	 * @return string
	 */
	private static function propertyFieldsToMysql(Reflection_Property $property)
	{
		return "id_" . $property->name;
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
		return "id";
	}

	//----------------------------------------------------------------------- propertyOnDeleteToMysql
	/**
	 * @param $property Reflection_Property
	 * @return string
	 */
	private static function propertyOnDeleteToMysql(Reflection_Property $property)
	{
		return $property->getAnnotation("composite")->value ? "CASCADE" : "RESTRICT";
	}

	//----------------------------------------------------------------------- propertyOnUpdateToMysql
	/**
	 * @return string
	 */
	private static function propertyOnUpdateToMysql()
	{
		return "RESTRICT";
	}

}
