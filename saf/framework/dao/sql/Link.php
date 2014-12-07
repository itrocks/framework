<?php
namespace SAF\Framework\Dao\Sql;

use SAF\Framework\Builder;
use SAF\Framework\Dao\Data_Link\Identifier_Map;
use SAF\Framework\Dao\Data_Link\Transactional;
use SAF\Framework\Dao\Option;
use SAF\Framework\Reflection\Reflection_Class;
use SAF\Framework\Sql\Builder\Select;
use SAF\Framework\Tools\Default_List_Data;
use SAF\Framework\Tools\Default_List_Row;
use SAF\Framework\Tools\List_Data;

/**
 * This is the common class for all SQL data links classes
 *
 * TODO LOW having both executeQuery() and query() is perhaps not a good idea
 */
abstract class Link extends Identifier_Map implements Transactional
{

	//--------------------------------------------------- Sql link configuration array keys constants
	const DATABASE = 'database';
	const HOST     = 'host';
	const LOGIN    = 'login';
	const PASSWORD = 'password';
	const TABLES   = 'tables';

	//---------------------------------------------------------------------------------------- $tables
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
	public function begin() {}

	//---------------------------------------------------------------------------------------- commit
	public function commit() {}

	//---------------------------------------------------------------------------------- executeQuery
	/**
	 * Execute an SQL query
	 *
	 * Sql_Link inherited classes must implement SQL query calls only into this method.
	 *
	 * @param $query string
	 * @return mixed the sql query result set (type and use may depends on each SQL data link)
	 */
	protected abstract function executeQuery($query);

	//----------------------------------------------------------------------------------------- fetch
	/**
	 * Fetch a result from a result set to an object
	 *
	 * Sql_Link inherited classes must implement fetching result rows only into this method.
	 * If $class_name is null, a stdClass object will be created.
	 *
	 * @param $result_set mixed  The result set : in most cases, will come from executeQuery()
	 * @param $class_name string The class name to store the result data into
	 * @return object
	 */
	protected abstract function fetch($result_set, $class_name = null);

	//-------------------------------------------------------------------------------------- fetchRow
	/**
	 * Fetch a result from a result set to an array
	 *
	 * @param $result_set mixed The result set : in most cases, will come from executeQuery()
	 * @return mixed[]
	 */
	protected abstract function fetchRow($result_set);

	//------------------------------------------------------------------------------------------ free
	/**
	 * Free a result set
	 *
	 * Sql_Link inherited classes must implement freeing result sets only into this method.
	 *
	 * @param $result_set mixed The result set : in most cases, will come from executeQuery()
	 */
	protected abstract function free($result_set);

	//--------------------------------------------------------------------------------- getColumnName
	/**
	 * Gets the column name from result set
	 *
	 * Sql_Link inherited classes must implement getting column name only into this method.
	 *
	 * @param $result_set mixed The result set : in most cases, will come from executeQuery()
	 * @param $index mixed The index of the column we want to get the SQL name from
	 * @return string
	 */
	protected abstract function getColumnName($result_set, $index);

	//-------------------------------------------------------------------------------- getColumnsCount
	/**
	 * Gets the column count from result set
	 *
	 * Sql_Link inherited classes must implement getting columns count only into this method
	 *
	 * @param $result_set mixed The result set : in most cases, will come from executeQuery()
	 * @return integer
	 */
	protected abstract function getColumnsCount($result_set);

	//---------------------------------------------------------------------------------- getRowsCount
	/**
	 * Gets the count of rows read / changed by the last query
	 *
	 * Sql_Link inherited classes must implement getting rows count only into this method
	 *
	 * @param $result_set mixed The result set : in most cases, will come from executeQuery()
	 * @param $clause     string The SQL query was starting with this clause
	 * @param $options    Option[] If set, will set the result into Dao_Count_Option::$count
	 * @return integer will return null if $options is set but contains no Dao_Count_Option
	 */
	protected abstract function getRowsCount($result_set, $clause, $options = []);

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
	// TODO LOW factorize this too big function
	public function select($object_class, $columns, $filter_object = null, $options = [])
	{
		$filter_object = $this->objectToProperties($filter_object);
		$list = new Default_List_Data($object_class, $columns);
		$columns[] = 'id';
		$sql_select_builder = new Select($object_class, $columns, $filter_object, $this, $options);
		$cols = [];
		foreach ($columns as $may_be_column => $column) {
			$cols[] = is_string($may_be_column) ? $may_be_column : $column;
		}
		$query = $sql_select_builder->buildQuery();
		$path_classes = $sql_select_builder->getJoins()->getClasses();
		$this->setContext(array_merge(
			$sql_select_builder->getJoins()->getClassNames(),
			$sql_select_builder->getJoins()->getLinkedTables()
		));
		$result_set = $this->executeQuery($query);
		$column_count = $this->getColumnsCount($result_set);
		if (isset($options)) {
			$this->getRowsCount($result_set, 'SELECT', $options);
		}
		$classes = [];
		$classes_index = [];
		$itoj = [];
		$column_names = [];
		$j = 0;
		for ($i = 0; $i < $column_count; $i++) {
			$column_names[$i] = $this->getColumnName($result_set, $i);
			if (strpos($column_names[$i], ':') == false) {
				$itoj[$i] = $j++;
			}
			else {
				$split = explode(':', $column_names[$i]);
				$column_names[$i] = $split[1];
				$main_property = $split[0];
				$hisj = isset($classes_index[$main_property]) ? $classes_index[$main_property] : null;
				if (!isset($hisj)) {
					$hisj = $j;
					$classes[$hisj] = $path_classes[$main_property];
					$classes_index[$main_property] = $j;
					$itoj[$i] = $j++;
				}
				else {
					$itoj[$i] = $hisj;
				}
			}
			if (substr($column_names[$i], 0, 3) === 'id_') {
				$column_names[$i] = substr($column_names[$i], 3);
			}
		}
		$first = true;
		/** @var $reflection_classes Reflection_Class[] */
		$reflection_classes = [];
		while ($result = $this->fetchRow($result_set)) {
			$row = [];
			for ($i = 0; $i < $column_count; $i++) {
				$j = $itoj[$i];
				if (!isset($classes[$j])) {
					$row[$cols[$j]] = $result[$i];
				}
				else {
					if (!isset($row[$cols[$j]])) {
						// TODO LOW try to get the object from object map to avoid multiple instances
						$row[$cols[$j]] = Builder::create($classes[$j]);
						if ($first && !isset($reflection_classes[$classes[$j]])) {
							$class = new Reflection_Class($classes[$j]);
							$class->accessProperties();
							$reflection_classes[$classes[$j]] = $class;
						}
					}
					$property_name = $column_names[$i];
					if ($property_name === 'id') {
						$this->setObjectIdentifier($row[$cols[$j]], $result[$i]);
					}
					else {
						$row[$cols[$j]]->$property_name = $result[$i];
					}
				}
			}
			$id = array_pop($row);
			$list->add(new Default_List_Row($object_class, $id, $row));
			$first = false;
		}
		return $list;
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
