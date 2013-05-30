<?php
namespace SAF\Framework;

use mysqli;

/**
 * Builds a Mysql_Table object from a mysqli connection and table name
 */
abstract class Mysql_Table_Builder_Mysqli
{

	//----------------------------------------------------------------------------------------- build
	/**
	 * Builds a Mysql_Table taken from database, using a mysqli connection
	 *
	 * @param $mysqli mysqli
	 * @param $table_name string
	 * @return Mysql_Table
	 */
	public static function build(mysqli $mysqli, $table_name)
	{
		$result = $mysqli->query("SHOW TABLE STATUS LIKE '$table_name'");
		$table = $result->fetch_object('SAF\Framework\Mysql_Table');
		$result->free();
		$result = $mysqli->query(
			"SELECT column_name `Field`,"
				. " IFNULL(CONCAT(column_type, ' CHARACTER SET ', character_set_name, ' COLLATE ', collation_name), column_type) `Type`,"
			. " is_nullable `Null`, column_key `Key`, column_default `Default`, extra `Extra`"
			. " FROM information_schema.columns"
			. " WHERE table_schema = DATABASE() AND table_name = '$table_name'"
		);
		while ($column = $result->fetch_object('SAF\Framework\Mysql_Column')) {
			$table->columns[$column->getName()] = $column;
		}
		$result->free();
		return $table;
	}

}
