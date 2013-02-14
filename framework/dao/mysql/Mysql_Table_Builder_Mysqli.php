<?php
namespace SAF\Framework;
use mysqli;

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
		$result = $mysqli->query("SHOW COLUMNS FROM `$table_name`");
		while ($column = $result->fetch_object('SAF\Framework\Mysql_Column')) {
			$table->columns[$column->getName()] = $column;
		}
		$result->free();
		return $table;
	}

}
