<?php
namespace SAF\Framework\Dao\Mysql;

use SAF\Framework\Tools\Contextual_Mysqli;

/**
 * Gives foreign keys to a column
 */
class Foreign_Keys_Tools
{

	//--------------------------------------------------------------------------------------- $mysqli
	/**
	 * @var Contextual_Mysqli
	 */
	private $mysqli;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Constructor needs a $mysqli to be connected to the MySQL server and database
	 *
	 * @param $mysqli Contextual_Mysqli
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
	 */
	public function toColumn($table, $column)
	{
		$database = $this->mysqli->selectedDatabase();
		$result = $this->mysqli->query("
			SELECT * FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
			WHERE REFERENCED_TABLE_SCHEMA = '$database'
			AND REFERENCED_TABLE_NAME = '$table'
			AND REFERENCED_COLUMN_NAME '$column'
		");
		while ($row = $result->fetch_assoc()) {
			
		}
	}

}
