<?php
namespace ITRocks\Framework\Dao\Mysql;

use mysqli;

/**
 * Builds a Table object from a mysqli connection and table name
 */
abstract class Table_Builder_Mysqli
{

	//----------------------------------------------------------------------------------------- build
	/**
	 * Builds a Table or Table[] taken from database, using a mysqli connection
	 *
	 * @param $mysqli        mysqli
	 * @param $table_name    string
	 * @param $database_name string
	 * @return Table|Table[] will be a single table only if $table_name is a
	 *         single table name without jokers characters
	 */
	public static function build(mysqli $mysqli, $table_name = null, $database_name = null)
	{
		$tables = [];
		$result = $mysqli->query(
			'SHOW TABLE STATUS'
			. (isset($database_name) ? (' IN ' . BQ . $database_name . BQ) : '')
			. (isset($table_name) ? (' LIKE ' . Q . $table_name . Q) : '')
		);
		while ($table = $result->fetch_object(Table::class)) {
			foreach (Column::buildTable($mysqli, $table->getName(), $database_name) as $column) {
				$table->addColumn($column);
			}
			foreach (
				Foreign_Key::buildTable($mysqli, $table->getName(), $database_name) as $foreign_key
			) {
				$table->addForeignKey($foreign_key);
			}
			$tables[] = $table;
		}
		$result->free();

		$unique = isset($table_name) && !str_contains($table_name, '%');

		return $unique ? reset($tables) : $tables;
	}

}
