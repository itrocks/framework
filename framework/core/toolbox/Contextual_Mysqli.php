<?php
namespace SAF\Framework;

use mysqli;

/**
 * Contextual mysqli class : this enables storage of context name for mysqli queries calls
 */
class Contextual_Mysqli extends mysqli
{

	//-------------------------------------------------------------------------------------- $context
	/**
	 * Query execution context
	 *
	 * @var string
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
	 * Big patch as this is needed for AOP, but AOP-Runkit does not work with php internal methods
	 *
	 * @todo patch for runkit-aop. remove as soon as possible
	 * @see mysqli::query
	 */
	public function query($query, $result_mode = MYSQLI_STORE_RESULT)
	{
		return parent::query($query, $result_mode);
	}

}
