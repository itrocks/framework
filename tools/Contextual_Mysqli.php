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
		$host = 'localhost', $user = null, $password = null, $database = null, $port = 3306,
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

	//----------------------------------------------------------------------------------------- query
	/**
	 * @param $query       string the SQL query
	 * @param $result_mode integer one of MYSQLI_*_RESULT constants
	 * @return mysqli_result|boolean false on failure, true or mysqli_result on success
	 * @see mysqli::query
	 */
	public function query($query, $result_mode = MYSQLI_STORE_RESULT)
	{
		// error_reporting patch to disable 'warning Error while sending QUERY packet' when mysql
		// disconnects. This may disable other warnings, but you always will have error / errno if
		// there is a mysqli error
		$reporting = error_reporting(E_ALL & ~E_WARNING);
		$result = parent::query($query, $result_mode);
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
	 */
	protected function queryError($query)
	{
		if (error_reporting()) {
			$error = $this->last_errno . ': ' . $this->last_error . '[' . $query . ']';
			trigger_error('Mysql logger error : ' . $error . ' on query ' . $query, E_USER_ERROR);
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

	//------------------------------------------------------------------------------ selectedDatabase
	/**
	 * Gets selected database name
	 *
	 * @return string
	 */
	public function selectedDatabase()
	{
		$result   = $this->query('SELECT DATABASE()');
		$database = $result->fetch_row()[0];
		$result->free();
		return $database;
	}

}
