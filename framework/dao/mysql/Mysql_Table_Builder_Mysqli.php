<?php
namespace SAF\Framework;

abstract class Mysql_Table_Builder_Mysqli
{

	//----------------------------------------------------------------------------------------- build
	public static function build(mysqli $mysqli, $table_name)
	{
		$result = $mysqli->query("SHOW TABLE STATUS LIKE '$table_name'");
		$table = $result->fetch_object(__NAMESPACE__ . "\\Mysql_Table");
		$result->free();
		$result = $mysqli->query("SHOW COLUMNS FROM '$table_name'");
		while ($column = $result->fetch_object(__NAMESPACE__ . "\\Mysql_Column")) {
			$table->columns[$column->getName()] = $column;
		}
		$result->free();
	}

}
