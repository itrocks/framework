<?php
namespace SAF\Framework;

/**
 * Some mysql foreign key builders methods
 */
abstract class Mysql_Foreign_Key_Builder
{

	//------------------------------------------------------------------------------------- buildLink
	/**
	 * Builds a Mysql_Foreign_Key for a column name that links to a given class name
	 *
	 * @param $table_name  string the table name
	 * @param $column_name string the column name linking to the foreign key (with or without "id_")
	 * @param $class_name  string the foreign class name
	 * @param $constraint  string CASCADE, NO ACTION, RESTRICT, SET NULL
	 * @return Mysql_Foreign_Key
	 */
	public static function buildLink($table_name, $column_name, $class_name, $constraint = "CASCADE")
	{
		if (substr($column_name, 0, 3) !== "id_") {
			$column_name = "id_" . $column_name;
		}
		$foreign_key = new Mysql_Foreign_Key();
		$class = new Reflection_Class(get_class($foreign_key));
		$class->accessProperties();
		$class->getProperty("Constraint")->setValue($foreign_key, $table_name . "." . $column_name);
		$class->getProperty("Fields")->setValue($foreign_key, $column_name);
		$class->getProperty("On_delete")->setValue($foreign_key, $constraint);
		$class->getProperty("On_update")->setValue($foreign_key, $constraint);
		$class->getProperty("Reference_fields")->setValue($foreign_key, "id");
		$class->getProperty("Reference_table")->setValue($foreign_key, Dao::storeNameOf($class_name));
		$class->accessPropertiesDone();
		return $foreign_key;
	}

}
