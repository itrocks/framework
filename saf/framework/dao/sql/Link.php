<?php
namespace SAF\Framework\Dao\Sql;

use SAF\Framework\Dao\Data_Link\Identifier_Map;
use SAF\Framework\Dao\Data_Link\Transactional;
use SAF\Framework\Dao\Option;
use SAF\Framework\Sql;
use SAF\Framework\Tools\Default_List_Data;
use SAF\Framework\Tools\List_Data;

/**
 * This is the common class for all SQL data links classes
 */
abstract class Link extends Identifier_Map implements Transactional
{

	//--------------------------------------------------- Sql link configuration array keys constants
	const DATABASE = 'database';
	const HOST     = 'host';
	const LOGIN    = 'login';
	const PASSWORD = 'password';
	const TABLES   = 'tables';

	//--------------------------------------------------------------------------------------- $tables
	/**
	 * Links each class name to it's storage table name
	 *
	 * @var string[] key is the class name, with or without namespace
	 */
	private $tables;

	//-------------------------------------------------------------------------------- $transactional
	/**
	 * Is the SQL database engine transactional ?
	 *
	 * @var boolean
	 */
	protected $transactional = false;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $parameters array
	 */
	public function __construct($parameters = null)
	{
		if (isset($parameters)) {
			$this->tables = isset($parameters[self::TABLES]) ? $parameters[self::TABLES] : [];
		}
	}

	//----------------------------------------------------------------------------------------- begin
	/**
	 * Begin transaction
	 */
	public function begin() {}

	//---------------------------------------------------------------------------------------- commit
	/**
	 * End transaction with commit
	 *
	 * @param $flush boolean
	 * @return boolean
	 */
	public function commit($flush = false) {}

	//----------------------------------------------------------------------------------------- fetch
	/**
	 * Fetch a result from a result set to an object
	 *
	 * Sql_Link inherited classes must implement fetching result rows only into this method.
	 * If $class_name is null, a stdClass object will be created.
	 *
	 * @param $result_set mixed  The result set : in most cases, will come from query()
	 * @param $class_name string The class name to store the result data into
	 * @return object
	 */
	public abstract function fetch($result_set, $class_name = null);

	//-------------------------------------------------------------------------------------- fetchRow
	/**
	 * Fetch a result from a result set to an array
	 *
	 * @param $result_set mixed The result set : in most cases, will come from query()
	 * @return mixed[]
	 */
	public abstract function fetchRow($result_set);

	//------------------------------------------------------------------------------------------ free
	/**
	 * Free a result set
	 *
	 * Sql_Link inherited classes must implement freeing result sets only into this method.
	 *
	 * @param $result_set mixed The result set : in most cases, will come from query()
	 */
	public abstract function free($result_set);

	//--------------------------------------------------------------------------------- getColumnName
	/**
	 * Gets the column name from result set
	 *
	 * Sql_Link inherited classes must implement getting column name only into this method.
	 *
	 * @param $result_set mixed The result set : in most cases, will come from query()
	 * @param $index mixed The index of the column we want to get the SQL name from
	 * @return string
	 */
	public abstract function getColumnName($result_set, $index);

	//------------------------------------------------------------------------------- getColumnsCount
	/**
	 * Gets the column count from result set
	 *
	 * Sql_Link inherited classes must implement getting columns count only into this method
	 *
	 * @param $result_set mixed The result set : in most cases, will come from query()
	 * @return integer
	 */
	public abstract function getColumnsCount($result_set);

	//---------------------------------------------------------------------------------- getRowsCount
	/**
	 * Gets the count of rows read / changed by the last query
	 *
	 * Sql_Link inherited classes must implement getting rows count only into this method
	 *
	 * @param $result_set mixed The result set : in most cases, will come from query()
	 * @param $clause     string The SQL query was starting with this clause
	 * @param $options    Option[] If set, will set the result into Dao_Count_Option::$count
	 * @return integer will return null if $options is set but contains no Dao_Count_Option
	 */
	public abstract function getRowsCount($result_set, $clause, $options = []);

	//----------------------------------------------------------------------------------------- query
	/**
	 * Executes an SQL query and returns the inserted record identifier (if applicable)
	 *
	 * @param $query      string
	 * @param $class_name string if set, the result will be object[] with read data
	 * @return integer|object[]
	 */
	abstract public function query($query, $class_name = null);

	//-------------------------------------------------------------------------------------- rollback
	/**
	 * Rollback current transaction
	 */
	public function rollback() {}

	//---------------------------------------------------------------------------------------- select
	/**
	 * Read selected columns only from data source, using optional filter
	 *
	 * @param $object_class  string class for the read object
	 * @param $columns       string[] the list of the columns names : only those properties will be
	 *        read. You can use 'column.sub_column' to get values from linked objects from the same
	 *        data source.
	 * @param $filter_object object|array source object for filter, set properties will be used for
	 *        search. Can be an array associating properties names to corresponding search value too.
	 * @param $options    Option[] some options for advanced search
	 * @return List_Data a list of read records. Each record values (may be objects) are stored in
	 *         the same order than columns.
	 */
	public function select($object_class, $columns, $filter_object = null, $options = [])
	{
		$list = new Default_List_Data($object_class, $columns);
		$select = new Select($object_class, $columns, $this);
		$query = $select->prepareQuery($filter_object, $options);
		$result_set = $this->query($query);
		if (isset($options)) {
			$this->getRowsCount($result_set, 'SELECT', $options);
		}
		return $select->fetchResultRows($result_set, $list);
	}

	//------------------------------------------------------------------------------------ setContext
	/**
	 * Set context for sql query
	 *
	 * @param $context_object string|string[] Can be a class name or an array of class names
	 */
	abstract public function setContext($context_object);

	//----------------------------------------------------------------------------------- storeNameOf
	/**
	 * @param $class_name string
	 * @return string
	 */
	public function storeNameOf($class_name)
	{
		if (isset($this->tables[$class_name])) {
			$store_name = $this->tables[$class_name];
		}
		else {
			$store_name = parent::storeNameOf($class_name);
			$this->tables[$class_name] = $store_name;
		}
		return $store_name;
	}

}
