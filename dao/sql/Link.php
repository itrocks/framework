<?php
namespace ITRocks\Framework\Dao\Sql;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao\Data_Link\Identifier_Map;
use ITRocks\Framework\Dao\Data_Link\Transactional;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Dao\Func\Column;
use ITRocks\Framework\Dao\Func\Dao_Function;
use ITRocks\Framework\Dao\Func\Expressions;
use ITRocks\Framework\Dao\Func\Where;
use ITRocks\Framework\Dao\Option;
use ITRocks\Framework\Reflection\Annotation\Class_;
use ITRocks\Framework\Reflection\Annotation\Property\Link_Annotation;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Tools\Default_List_Data;
use ITRocks\Framework\Tools\List_Data;

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
	const PORT     = 'port';
	const SOCKET   = 'socket';
	const TABLES   = 'tables';

	//--------------------------------------------------------------------------------------- $tables
	/**
	 * Links each class name to its storage table name
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
	public function __construct(array $parameters = null)
	{
		$this->tables = isset($parameters[self::TABLES]) ? $parameters[self::TABLES] : [];
	}

	//----------------------------------------------------------------------------------------- begin
	/**
	 * Begin transaction
	 */
	public function begin()
	{
		$this->after_commit = [];
	}

	//---------------------------------------------------------------------------------------- commit
	/**
	 * End transaction with commit
	 *
	 * @param $flush boolean
	 * @return boolean
	 */
	public function commit($flush = false)
	{
		$this->afterCommit();
		return false;
	}

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
	 * @param $index      mixed The index of the column we want to get the SQL name from
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
	 * @param $clause     string The SQL query was starting with this clause
	 * @param $options    Option|Option[] If set, will set the result into Dao_Count_Option::$count
	 * @param $result_set mixed The result set : in most cases, will come from query()
	 * @return integer will return null if $options is set but contains no Dao_Count_Option
	 */
	public abstract function getRowsCount($clause, $options = [], $result_set = null);

	//------------------------------------------------------------------------------------ popContext
	/**
	 * Pop context for sql query
	 */
	abstract public function popContext();

	//----------------------------------------------------------------------------------- pushContext
	/**
	 * Push context for sql query
	 *
	 * @param $context_object string|string[] Can be a class name or an array of class names
	 */
	abstract public function pushContext($context_object);

	//----------------------------------------------------------------------------------------- query
	/**
	 * Executes an SQL query and returns the inserted record identifier (if applicable)
	 *
	 * @param $query      string
	 * @param $class_name string if set, the result will be object[] with read data
	 * @param $result_set mixed The result set associated to the data link, if $class_name is constant
	 * @return mixed depends on $class_name specific constants used
	 */
	abstract public function query($query, $class_name = null, &$result_set = null);

	//---------------------------------------------------------------------------------- readProperty
	/**
	 * Reads the value of a property from the data store
	 * Used only when @dao dao_name is used on a property which is not a @var @link (simple values)
	 *
	 * @param $object        object object from which to read the value of the property
	 * @param $property_name string the name of the property
	 * @return mixed the read value for the property read from the data link. null if no value stored
	 */
	public function readProperty($object, $property_name)
	{
		user_error(
			'@dao : property ' . get_class($object) . '::' . $property_name
			. ' cannot be read alone into a ' . get_class($this) . ' data link',
			E_USER_ERROR
		);
		return null;
	}

	//-------------------------------------------------------------------------------------- rollback
	/**
	 * Rollback current transaction
	 */
	public function rollback()
	{
	}

	//---------------------------------------------------------------------------------------- select
	/**
	 * Read selected columns only from data source, using optional filter
	 *
	 * @param $object_class  string class for the read object
	 * @param $properties    string[]|string|Column[] the list of property paths : only those
	 *                       properties will be read. You can use Dao\Func\Column sub-classes to get
	 *                       result of functions.
	 * @param $filter_object object|array source object for filter, set properties will be used for
	 *                       search. Can be an array associating properties names to corresponding
	 *                       search value too.
	 * @param $options       Option|Option[] some options for advanced search
	 * @return List_Data a list of read records. Each record values (may be objects) are stored in
	 *                   the same order than columns.
	 */
	public function select($object_class, $properties, $filter_object = null, $options = [])
	{
		if (is_string($object_class)) {
			$object_class = Builder::className($object_class);
		}
		if (!is_array($options)) {
			$options = $options ? [$options] : [];
		}
		if (!is_array($properties)) {
			$properties = $properties ? [$properties] : [];
		}
		list($double_pass, $list) = $this->selectOptions($options, $properties);
		if (!isset($list)) {
			$list = $this->selectList($object_class, $properties);
		}

		if ($double_pass) {
			$new_filter_object = $this->selectFirstPass(
				$object_class, $properties, $filter_object, $options
			);
			if (!$new_filter_object) {
				return $list;
			}
			$filter_object = $filter_object
				? Func::andOp([$filter_object, $new_filter_object])
				: $new_filter_object;
		}

		$select     = new Select($object_class, $properties, $this);
		$query      = $select->prepareQuery($filter_object, $options);
		$result_set = $this->query($query);
		$select->doneQuery();
		if ($options && !$double_pass) {
			$this->getRowsCount('SELECT', $options, $result_set);
		}
		return $select->fetchResultRows($result_set, $list);
	}

	//------------------------------------------------------------------------------- selectFirstPass
	/**
	 * @param $object_class  string class for the read object
	 * @param $properties    string[]|Column[] the list of property paths : only those properties will
	 *                       be read. You can use Dao\Func\Column sub-classes to get result of
	 *                       functions.
	 * @param $filter_object object|array source object for filter, set properties will be used for
	 *                       search. Can be an array associating properties names to matching search
	 *                       value too.
	 * @param $options       Option[] some options for advanced search
	 * @return Where|null
	 */
	private function selectFirstPass(
		$object_class, array $properties, $filter_object, array &$options
	) {
		$properties = $this->selectFirstPassProperties($object_class, $properties, $options);
		$select     = new Select($object_class, $properties, $this);
		$query      = $select->prepareQuery($filter_object, $options);
		$result_set = true;
		if ($properties) {
			$read_lines_filter = $this->query($query, AS_ARRAY, $result_set);
			foreach ($read_lines_filter as $key => &$value) {
				foreach ($value as $key2 => $value2) {
					if (is_null($value2)) {
						unset($value[$key2]);
					}
				}
				$read_lines_filter[$key] = (count($value) > 1) ? Func::andOp($value) : $value;
			}
		}
		else {
			$read_lines_filter = $this->query($query, AS_VALUES, $result_set);
		}
		$select->doneQuery();
		if ($options && $result_set) {
			$this->getRowsCount('SELECT', $options, $result_set);
			$this->free($result_set);
			// remove options for second path
			foreach ($options as $key => $option) {
				if (($option instanceof Option\Count) || ($option instanceof Option\Limit)) {
					unset($options[$key]);
				}
			}
		}
		return $read_lines_filter
			? ($properties ? Func::orOp($read_lines_filter) : Func::in($read_lines_filter))
			: null;
	}

	//--------------------------------------------------------------------- selectFirstPassProperties
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object_class  string class for the read object
	 * @param $properties    string[]|Column[] the list of property paths : only those properties will
	 *                       be read. You can use Dao\Func\Column sub-classes to get result of
	 *                       functions.
	 * @param $options       Option[] some options for advanced search
	 * @return string[] path of the properties we keep for the first pass, for correct rows counting
	 */
	private function selectFirstPassProperties($object_class, array $properties, array $options)
	{
		if ($group_by = Option\Group_By::in($options)) {
			return $group_by->properties;
		}
		$result = [];
		foreach ($properties as $key => $property_path) {
			if (is_string($key) && !is_string($property_path)) {
				$property_path = $key;
			}
			$path = '';
			foreach (explode(DOT, $property_path) as $property_name) {
				$path .= ($path ? DOT : '') . $property_name;
				if (substr($path, -1) === ')') {
					continue;
				}
				/** @noinspection PhpUnhandledExceptionInspection class and property must be valid */
				$property = new Reflection_Property($object_class, $path);
				$link     = Link_Annotation::of($property);
				if (!$link->is(Link_Annotation::COLLECTION, Link_Annotation::MAP)) {
					continue;
				}
				$class = $property->getType()->asLinkClass();
				if ($link->isCollection() && Class_\Link_Annotation::of($class)->value) {
					$property_path = $path . DOT . $class->getLinkProperty()->name . '.id';
				}
				else {
					$property_path = $path . '.id';
				}
				$result[$property_path] = $property_path;
			}
		}
		return $result;
	}

	//------------------------------------------------------------------------------------ selectList
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object_class string class for the read object
	 * @param $columns      string[]|Column[] the list of the columns names : only those properties
	 *                      will be read. You can use 'column.sub_column' to get values from linked
	 *                      objects from the same data source. You can use Dao\Func\Column sub-classes
	 *                      to get result of functions.
	 * @return Default_List_Data
	 */
	private function selectList($object_class, array $columns)
	{
		$functions  = [];
		$properties = [];
		foreach ($columns as $key => $column) {
			$property_path = is_object($column) ? $key : $column;
			if (Expressions::isFunction($property_path)) {
				$expression    = Expressions::$current->cache[$property_path];
				$property_path = $expression->property_path;
			}
			/** @noinspection PhpUnhandledExceptionInspection property must be valid */
			$properties[$property_path] = new Reflection_Property($object_class, $property_path);
			$functions[$property_path]  = ($column instanceof Dao_Function) ? $column : null;
		}
		return new Default_List_Data($object_class, $properties, $functions);
	}

	//--------------------------------------------------------------------------------- selectOptions
	/**
	 * @param $options Option[] some options for advanced search
	 * @param $columns string[]|Column[] the list of the columns names : only those properties will be
	 *                 read. You can use 'column.sub_column' to get values from linked objects from
	 *                 the same data source. You can use Dao\Func\Column sub-classes to get result of
	 *                 functions.
	 * @return array [boolean $double_pass, array $list]
	 */
	private function selectOptions(array $options, array $columns)
	{
		$double_pass = false;
		$list        = null;
		foreach ($options as $option) {
			if ($option instanceof Option\Double_Pass) {
				foreach ($columns as $column_key => $column) {
					if (is_object($column) && is_string($column_key)) {
						$column = $column_key;
					}
					if (is_string($column) && strpos($column, DOT)) {
						$double_pass = true;
						break;
					}
				}
			}
			elseif (($option instanceof Option\Array_Result) || ($option === AS_ARRAY)) {
				$list = [];
			}
			elseif (is_callable($option)) {
				$list = $option;
			}
		}
		return [$double_pass, $list];
	}

	//----------------------------------------------------------------------------------- storeNameOf
	/**
	 * @param $class_name string
	 * @return string
	 */
	public function storeNameOf($class_name)
	{
		if (!isset($this->tables[$class_name])) {
			$this->tables[$class_name] = parent::storeNameOf($class_name);
		}
		return $this->tables[$class_name];
	}

	//-------------------------------------------------------------------------------------- truncate
	/**
	 * Truncates the data-set storing $class_name objects
	 *
	 * All data is deleted
	 *
	 * @param $class_name string
	 */
	public function truncate($class_name)
	{
		$this->pushContext($class_name);
		$table_name = $this->storeNameOf($class_name);
		$this->query('TRUNCATE TABLE ' . BQ . $table_name . BQ);
		$this->popContext();
	}

	//--------------------------------------------------------------------------------- writeProperty
	/**
	 * Writes the value of a property into the data store
	 * Used only when @dao dao_name is used on a property which is not a @var @link (simple values)
	 *
	 * @param $object        object object from which to get the value of the property
	 * @param $property_name string the name of the property
	 * @param $value         mixed if set (recommended), the value to be stored. default in $object
	 */
	public function writeProperty($object, $property_name, $value = null)
	{
		user_error(
			'@dao : property ' . get_class($object) . '::' . $property_name
				. ' cannot be written alone into a ' . get_class($this) . ' data link',
			E_USER_ERROR
		);
	}

}
