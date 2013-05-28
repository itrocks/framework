<?php
namespace SAF\Framework;

/**
 * Some mysql column builders methods : if for a main identifier column, for a link column
 */
abstract class Mysql_Column_Builder
{

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

	//------------------------------------------------------------------------------------- buildLink
	/**
	 * Builds a Mysql_Column object for a standard "id_*" link column
	 *
	 * @param $column_name string
	 * @return Mysql_Column
	 */
	public static function buildLink($column_name)
	{
		$column = new Mysql_Column();
		$class = Reflection_Class::getInstanceOf(get_class($column));
		$class->accessProperties();
		$class->getProperty("Field")->setValue($column, $column_name);
		$class->getProperty("Type")->setValue($column, "bigint(18) unsigned");
		$class->getProperty("Null")->setValue($column, "NO");
		$class->getProperty("Default")->setValue($column, 0);
		$class->accessPropertiesDone();
		return $column;
	}

}
