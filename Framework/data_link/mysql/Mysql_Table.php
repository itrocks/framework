<?php

class Mysql_Table implements Dao_Table
{

	//------------------------------------------------------------------------------------- getFields
	public static function getFields($data_link, $object_class)
	{
		$result_set = mysql_query(
			"SHOW FIELDS FROM `" . Sql_Table::classToTableName($object_class) . "`",
			$data_link->getConnection()
		);
		while ($object = mysql_fetch_object($result_set, "Mysql_Field")) {
			$fields[$object->getName()] = $object;
		}
		mysql_free_result($result_set);
		return $fields;
	}

}
