<?php
namespace ITRocks\Framework\Dao\Mysql;

use ITRocks\Framework\Tools\Contextual_Mysqli;

/**
 * Gives foreign keys to a column
 */
class Foreign_Keys_Tools
{

	//--------------------------------------------------------------------------------------- $mysqli
	/**
	 * @var Contextual_Mysqli
	 */
	private Contextual_Mysqli $mysqli;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Constructor needs a $mysqli to be connected to the MySQL server and database
	 *
	 * @param $mysqli Contextual_Mysqli|null
	 */
	public function __construct(Contextual_Mysqli $mysqli = null)
	{
		if (isset($mysqli)) {
			$this->mysqli = $mysqli;
		}
	}

	//-------------------------------------------------------------------------------------- toColumn
	/**
	 * Returns all foreign keys to a column
	 *
	 * @param $table  string
	 * @param $column string
	 */
	public function toColumn(string $table, string $column) : void
	{
		// TODO Some things, I assume
		/*
		$database = $this->mysqli->selectedDatabase();
		$result = $this->mysqli->query("
			SELECT * FROM `information_schema`.`key_column_usage`
			WHERE `referenced_table_schema` = '$database'
			AND `referenced_table_name` = '$table'
			AND `referenced_column_name` = '$column'
		");
		while ($row = $result->fetch_assoc()) {
		}
		*/
	}

}
