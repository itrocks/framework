<?php
namespace SAF\Framework;

use mysqli;
use mysqli_result;

/**
 * Contextual mysqli class : this enables storage of context name for mysqli queries calls
 */

/** @noinspection PhpDocSignatureInspection Inspector bug on query that returns a value ! */
class Contextual_Mysqli extends mysqli
{

	//-------------------------------------------------------------------------------------- $context
	/**
	 * Query execution context : the class name or list of class names which could be concerned
	 * by the current executed query (if set)
	 *
	 * @var string|string[]
	 */
	public $context;

	//---------------------------------------------------------------------------------------- exists
	/**
	 * Checks if a table or column exists
	 *
	 * @param $table_name string
	 * @param $column_name string
	 * @return boolean true if the object exists in current database
	 */
	public function exists($table_name, $column_name = null)
	{
		if (isset($column_name)) {
			$table = Mysql_Table_Builder_Mysqli::build($this, $table_name);
			return $table->hasColumn($column_name);
		}
		else {
			$res = $this->query("SHOW TABLES");
			/** @var $table Mysql_Table */
			while ($table = $res->fetch_row()) {
				if ($table[0] == $table_name) {
					$res->free();
					return true;
				}
			}
			$res->free();
			return false;
		}
	}

	//----------------------------------------------------------------------------------------- query
	/**
	 * @see mysqli::query
	 * @todo Big patch as this is needed for AOP, but AOP-runkit does not work with php internal
	 * methods. Should be removed when a workaround is found
	 *
	 * @return mysqli_result|boolean false on failure, true or mysqli_result on success
	 */
	public function query($query, $result_mode = MYSQLI_STORE_RESULT)
	{
		return parent::query($query, $result_mode);
	}

}
