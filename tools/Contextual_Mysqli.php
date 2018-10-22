<?php
namespace ITRocks\Framework\Tools;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Mysql\Mysql_Error_Exception;
use ITRocks\Framework\Dao\Mysql\Table;
use ITRocks\Framework\Dao\Mysql\Table_Builder_Mysqli;
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

	//------------------------------------------------------------------------------------- $database
	/**
	 * @var string
	 */
	public $database;

	//----------------------------------------------------------------------------------------- $host
	/**
	 * @var string
	 */
	public $host;

	//----------------------------------------------------------------------------------- $last_errno
	/**
	 * Last error number : mysqli::$errno is reset to 0 immediately when you read it.
	 * This one is kept until the next query.
	 *
	 * @var integer
	 */
	public $last_errno;

	//----------------------------------------------------------------------------------- $last_error
	/**
	 * Last error message : mysqli::$error is reset to empty immediately when you read it.
	 * This one is kept until the next query.
	 *
	 * @var string
	 */
	public $last_error;

	//------------------------------------------------------------------------------------- $password
	/**
	 * @var string
	 */
	public $password;

	//----------------------------------------------------------------------------------------- $port
	/**
	 * @var integer
	 */
	public $port;

	//--------------------------------------------------------------------------------------- $socket
	/**
	 * @var integer
	 */
	public $socket;

	//----------------------------------------------------------------------------------------- $user
	/**
	 * @var string
	 */
	public $user;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Opens a new connection to the MySQL server
	 *
	 * @param $host     string
	 * @param $user     string
	 * @param $password string
	 * @param $database string
	 * @param $port     integer
	 * @param $socket   string
	 */
	public function __construct(
		$host = '127.0.0.1', $user = null, $password = null, $database = null, $port = 3306,
		$socket = null
	) {
		parent::__construct($host, $user, $password, $database, $port);
		$this->host     = $host;
		$this->user     = $user;
		$this->password = $password;
		$this->database = $database;
		$this->port     = $port;
		$this->socket   = $socket;
	}

	//-------------------------------------------------------------------------------- databaseExists
	/**
	 * @param $database_name string default is current database
	 * @return boolean
	 */
	public function databaseExists($database_name = null)
	{
		if (!$database_name) {
			$database_name = $this->database;
		}
		$res = $this->query("SHOW DATABASES LIKE '%$database_name%'");
		$row = $res->fetch_row();
		$res->free();
		return boolval($row);
	}

	//------------------------------------------------------------------------------------------ drop
	/**
	 * Drop a table or column
	 *
	 * @param $table_name  string
	 * @param $column_name string|null If set, drop this column instead of the table
	 * @return boolean true if dropped, false if was not already existing
	 * @throws Mysql_Error_Exception
	 */
	public function drop($table_name, $column_name = null)
	{
		if ($this->exists($table_name, $column_name)) {
			$this->query(
				isset($column_name)
					? "ALTER TABLE `$table_name` DROP `$column_name`"
					: "DROP TABLE `$table_name`"
			);
			return true;
		}
		return false;
	}

	//---------------------------------------------------------------------------------------- exists
	/**
	 * Checks if a table or column exists
	 *
	 * @param $table_name  string
	 * @param $column_name string
	 * @return boolean true if the object exists in current database
	 * @throws Mysql_Error_Exception
	 */
	public function exists($table_name, $column_name = null)
	{
		if (isset($column_name)) {
			$table = Table_Builder_Mysqli::build($this, $table_name);
			return $table->hasColumn($column_name);
		}
		else {
			$res = $this->query("SHOW TABLES LIKE '$table_name'");
			/** @var $table Table */
			while ($table = $res->fetch_row()) {
				if (reset($table) === $table_name) {
					$res->free();
					return true;
				}
			}
			$res->free();
			return false;
		}
	}

	//---------------------------------------------------------------------------------- getDatabases
	/**
	 * Gets all visible databases names
	 *
	 * @return string[]
	 * @throws Mysql_Error_Exception
	 */
	public function getDatabases()
	{
		$databases = [];
		$res       = $this->query('SHOW DATABASES');
		while ($row = $res->fetch_row()) {
			$databases[] = $row[0];
		}
		$res->free();
		return $databases;
	}

	//------------------------------------------------------------------------------------- getTables
	/**
	 * Gets all existing tables names from current database
	 *
	 * @return string[]
	 * @throws Mysql_Error_Exception
	 */
	public function getTables()
	{
		$tables = [];
		$res    = $this->query('SHOW TABLES');
		while ($row = $res->fetch_row()) {
			$tables[] = $row[0];
		}
		$res->free();
		return $tables;
	}

	//-------------------------------------------------------------------------------------------- is
	/**
	 * Returns true if the two mysqli connexions are the same one
	 *
	 * @param $mysqli Contextual_Mysqli
	 * @return boolean
	 */
	public function is(Contextual_Mysqli $mysqli)
	{
		return ($mysqli->thread_id === $this->thread_id) && ($mysqli->host_info === $this->host_info);
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
		return (strtoupper(substr(trim($query), 0, 6)) === 'DELETE');
	}

	//------------------------------------------------------------------------------- isExplainSelect
	/**
	 * Returns true if the query is an EXPLAIN SELECT
	 *
	 * @param $query string
	 * @return boolean
	 */
	public function isExplainSelect($query)
	{
		return strtoupper(substr(trim($query), 0, 14)) === 'EXPLAIN SELECT';
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
		return (strtoupper(substr(trim($query), 0, 11)) === 'INSERT INTO');
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
		return (strtoupper(substr(trim($query), 0, 6)) === 'SELECT');
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
		return (strtoupper(substr(trim($query), 0, 8)) === 'TRUNCATE');
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
		return (strtoupper(substr(trim($query), 0, 6)) === 'UPDATE');
	}

	//------------------------------------------------------------------------------------ lastUpdate
	/**
	 * Gets the date of the last update of the table of the given class
	 *
	 * Returns the date of the last update of the table formatted using 'Y-m-d H:i:s'.
	 * Returns null if the information cannot be retrieved.
	 *
	 * @param $class_name string The name of the class.
	 * @return string|null ISO date
	 * @throws Mysql_Error_Exception
	 */
	public function lastUpdate($class_name)
	{
		$table_name = Dao::current()->storeNameOf($class_name);
		$query      = "
SELECT `UPDATE_TIME`
FROM `information_schema`.`TABLES`
WHERE `TABLE_NAME` = '$table_name'
AND `TABLE_SCHEMA` = '{$this->database}'
AND `UPDATE_TIME` IS NOT NULL
		";

		$information = $this->query($query)->fetch_assoc();

		return isset($information['UPDATE_TIME'])
			? $information['UPDATE_TIME']
			: null;
	}

	//----------------------------------------------------------------------------------------- query
	/**
	 * @param $query       string the SQL query
	 * @param $result_mode integer one of MYSQLI_*_RESULT constants
	 * @return mysqli_result|boolean false on failure, true or mysqli_result on success
	 * @see mysqli::query
	 * @throws Mysql_Error_Exception
	 */
	public function query($query, $result_mode = MYSQLI_STORE_RESULT)
	{
		// error_reporting patch to disable 'warning Error while sending QUERY packet' when mysql
		// disconnects. This may disable other warnings, but you always will have error / errno if
		// there is a mysqli error
		$reporting = error_reporting(E_ALL & ~E_WARNING);
		$result    = parent::query($query, $result_mode);
		error_reporting($reporting);
		$this->last_errno = $this->errno;
		$this->last_error = $this->error;
		if (($result === false) && !$this->last_errno && $this->isSelect($query)) {
			$this->last_errno = 999;
			$this->last_error = 'Unknown error';
		}
		if ($this->last_errno || $this->last_error) {
			$result = $this->queryError($query);
		}
		return $result;
	}

	//------------------------------------------------------------------------------------ queryError
	/**
	 * @param $query string
	 * @return mysqli_result|boolean false, but other errors managers may change this
	 * @throws Mysql_Error_Exception
	 */
	protected function queryError($query)
	{
		if (error_reporting()) {
			throw new Mysql_Error_Exception($this->last_errno, $this->last_error, $query);
		}
		return false;
	}

	//------------------------------------------------------------------------------------- reconnect
	/**
	 * Reconnects to the mysql server
	 *
	 * You can't reconnect an existing mysqli connexion : it will be replaced.
	 *
	 * @return boolean true if reconnect worked, false in case of connect error
	 */
	public function reconnect()
	{
		$this->connect(
			$this->host, $this->user, $this->password, $this->database, $this->port, $this->socket
		);
		return !$this->connect_errno && !$this->connect_error;
	}

	//----------------------------------------------------------------------------------- renameTable
	/**
	 * @param $old_name string
	 * @param $new_name string
	 * @throws Mysql_Error_Exception
	 */
	public function renameTable($old_name, $new_name)
	{
		$this->query("RENAME TABLE `$old_name` TO `$new_name`");
	}

	//------------------------------------------------------------------------------ selectedDatabase
	/**
	 * Gets selected database name
	 *
	 * @return string
	 * @throws Mysql_Error_Exception
	 */
	public function selectedDatabase()
	{
		$result   = $this->query('SELECT DATABASE()');
		$database = $result->fetch_row()[0];
		$result->free();
		return $database;
	}

}
