<?php
namespace SAF\Framework\Tools;

use mysqli;
use mysqli_result;
use SAF\Framework\Dao\Mysql\Table;
use SAF\Framework\Dao\Mysql\Table_Builder_Mysqli;

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

	/**
	 * Last error number : mysqli::$errno is reset to 0 immediately when you read it.
	 * This one is kept until the next query.
	 *
	 * @var integer
	 */
	public $last_errno;

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
			$table = Table_Builder_Mysqli::build($this, $table_name);
			return $table->hasColumn($column_name);
		}
		else {
			$res = $this->query('SHOW TABLES');
			/** @var $table Table */
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

	//------------------------------------------------------------------------------------- getTables
	/**
	 * Gets all existing tables names from current database
	 *
	 * @return string[]
	 */
	public function getTables()
	{
		$tables = [];
		$res = $this->query('SHOW TABLES');
		while ($row = $res->fetch_row()) {
			$tables[] = $row[0];
		}
		return $tables;
	}

	//-------------------------------------------------------------------------------------- isDelete
	/**
	 * Returns true if the query is a DELETE
	 *
	 * @param $query string
	 * @return boolean
	 */
	public function isDelete($query)
	{
		return (strtoupper(substr(trim($query), 0, 7)) === 'DELETE ');
	}

	//-------------------------------------------------------------------------------------- isInsert
	/**
	 * Returns true if the query is an INSERT
	 *
	 * @param $query string
	 * @return boolean
	 */
	public function isInsert($query)
	{
		return (strtoupper(substr(trim($query), 0, 12)) === 'INSERT INTO ');
	}

	//-------------------------------------------------------------------------------------- isSelect
	/**
	 * Returns true if the query is a SELECT
	 *
	 * @param $query string
	 * @return boolean
	 */
	public function isSelect($query)
	{
		return (strtoupper(substr(trim($query), 0, 7)) === 'SELECT ');
	}

	//------------------------------------------------------------------------------------ isTruncate
	/**
	 * Returns true if the query is a TRUNCATE
	 *
	 * @param $query string
	 * @return boolean
	 */
	public function isTruncate($query)
	{
		return (strtoupper(substr(trim($query), 0, 9)) === 'TRUNCATE ');
	}

	//-------------------------------------------------------------------------------------- isUpdate
	/**
	 * Returns true if the query is an UPDATE
	 *
	 * @param $query string
	 * @return boolean
	 */
	public function isUpdate($query)
	{
		return (strtoupper(substr(trim($query), 0, 7)) === 'UPDATE ');
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
		$result = parent::query($query, $result_mode);
		$this->last_errno = $this->errno;
		return $result;
	}

}
