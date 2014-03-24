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
	 * Builds a Mysql_Table or Mysql_Table[] taken from database, using a mysqli connection
	 *
	 * @param $mysqli        mysqli
	 * @param $table_name    string
	 * @param $database_name string
	 * @return Mysql_Table|Mysql_Table[] will be a single table only if $table_name is a
	 *         single table name without jokers characters
	 */
	public static function build(mysqli $mysqli, $table_name = null, $database_name = null)
	{
		$tables = [];
		$result = $mysqli->query(
			'SHOW TABLE STATUS'
			. (isset($database_name) ? (' IN ' . DQ . $database_name . DQ) : '')
			. (isset($table_name) ? (' LIKE ' . DQ . $table_name . DQ) : '')
		);
		/** @var $table Mysql_Table */
		while ($table = $result->fetch_object(Mysql_Table::class)) {
			foreach (Mysql_Column::buildTable($mysqli, $table->getName(), $database_name) as $column) {
				$table->addColumn($column);
			}
			foreach (
				Mysql_Foreign_Key::buildTable($mysqli, $table->getName(), $database_name) as $foreign_key
			) {
				$table->addForeignKey($foreign_key);
			}
			$tables[] = $table;
		}
		$result->free();

		$unique = isset($table_name)
			&& (strpos($table_name, '%') === false) && (strpos($table_name, '_') === false);

		return $unique ? reset($tables) : $tables;
	}

}
