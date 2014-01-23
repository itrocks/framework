<?php
namespace SAF\Framework;

/**
 * Some mysql index builders methods
 */
abstract class Mysql_Index_Builder
{

	//------------------------------------------------------------------------------------- buildLink
	/**
	 * Builds a Mysql_Index for a column name that is a link to another class
	 *
	 * @param $column_name string the column name used to create the index (with or without "id_")
	 * @return Mysql_Index
	 */
	public static function buildLink($column_name)
	{
		if (substr($column_name, 0, 3) !== "id_") {
			$column_name = "id_" . $column_name;
		}
		$key = new Mysql_Key();
		$class = new Reflection_Class(get_class($key));
		$class->accessProperties();
		$class->getProperty("Column_name")->setValue($key, $column_name);
		$class->getProperty("Key_name")->setValue($key, $column_name);
		$class->accessPropertiesDone();
		$index = new Mysql_Index();
		$index->keys[] = $key;
		return $index;
	}

}
