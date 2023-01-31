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
use ITRocks\Framework\Reflection\Interfaces\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Tools\Default_List_Data;
use ITRocks\Framework\Tools\List_Data;
use ReflectionException;

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
	private array $tables;

	//-------------------------------------------------------------------------------- $transactional
	/**
	 * Is the SQL database engine transactional ?
	 *
	 * @var boolean
	 */
	protected bool $transactional = false;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $parameters array|null
	 */
	public function __construct(array $parameters = null)
	{
		$this->tables = $parameters[self::TABLES] ?? [];
	}

	//----------------------------------------------------------------------------------------- begin
	/**
	 * Begin transaction
	 *
	 * @return ?boolean
	 */
	public function begin() : ?bool
	{
		$this->after_commit = [];
		return true;
	}

	//---------------------------------------------------------------------------------------- commit
	/**
	 * End transaction with commit
	 *
	 * @param $flush boolean
	 * @return ?boolean
	 */
	public function commit(bool $flush = false) : ?bool
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
	 * @param $result_set mixed The result set : in most cases, will come from query()
	 * @param $class_name string|null The class name to store the result data into
	 * @return ?object
	 */
	public abstract function fetch(mixed $result_set, string $class_name = null) : ?object;

	//-------------------------------------------------------------------------------------- fetchRow
	/**
	 * Fetch a result from a result set to an array
	 *
	 * @param $result_set mixed The result set : in most cases, will come from query()
	 * @return ?array
	 */
	public abstract function fetchRow(mixed $result_set) : ?array;

	//------------------------------------------------------------------------------------------ free
	/**
	 * Free a result set
	 *
	 * Sql_Link inherited classes must implement freeing result sets only into this method.
	 *
	 * @param $result_set mixed The result set : in most cases, will come from query()
	 */
	public abstract function free(mixed $result_set) : void;

	//--------------------------------------------------------------------------------- getColumnName
	/**
	 * Gets the column name from result set
	 *
	 * Sql_Link inherited classes must implement getting column name only into this method.
	 *
	 * @param $result_set mixed The result set : in most cases, will come from query()
	 * @param $index      int|string The index of the column we want to get the SQL name from
	 * @return string
	 */
	public abstract function getColumnName(mixed $result_set, int|string $index) : string;

	//------------------------------------------------------------------------------- getColumnsCount
	/**
	 * Gets the column count from result set
	 *
	 * Sql_Link inherited classes must implement getting columns count only into this method
	 *
	 * @param $result_set mixed The result set : in most cases, will come from query()
	 * @return integer
	 */
	public abstract function getColumnsCount(mixed $result_set) : int;

	//---------------------------------------------------------------------------------- getRowsCount
	/**
	 * Gets the count of rows read / changed by the last query
	 *
	 * Sql_Link inherited classes must implement getting rows count only into this method
	 *
	 * @param $clause     string The SQL query was starting with this clause
	 * @param $options    Option|Option[] If set, will set the result into Dao_Count_Option::$count
	 * @param $result_set mixed The result set : in most cases, will come from query()
	 * @return ?integer will return null if $options is set but contains no Dao_Count_Option
	 */
	public abstract function getRowsCount(
		string $clause, array|Option $options = [], mixed $result_set = null
	) : ?int;

	//------------------------------------------------------------------------------------ popContext
	/**
	 * Pop context for sql query
	 */
	abstract public function popContext() : array|string;

	//----------------------------------------------------------------------------------- pushContext
	/**
	 * Push context for sql query
	 *
	 * @param $context_object string|string[] Can be a class name or an array of class names
	 */
	abstract public function pushContext(array|string $context_object) : void;

	//----------------------------------------------------------------------------------------- query
	/**
	 * Executes an SQL query and returns the inserted record identifier (if applicable)
	 *
	 * @param $query      string
	 * @param $class_name class-string<T>|null if set, the result will be 'object[]' with read data
	 * @param $result     mixed The result set associated to the data link, if $class_name is constant
	 *        Call $query with $result = true to store the result set into $result
	 * @return mixed|T[] depends on $class_name specific constants used
	 * @template T
	 */
	abstract public function query(string $query, string $class_name = null, mixed &$result = null)
		: mixed;

	//---------------------------------------------------------------------------------- readProperty
	/**
	 * Reads the value of a property from the data store
	 * Used only when @dao dao_name is used on a property which is not a @var @link (simple values)
	 *
	 * @param $object        object object from which to read the value of the property
	 * @param $property_name string the name of the property
	 * @return mixed the read value for the property read from the data link. null if no value stored
	 */
	public function readProperty(object $object, string $property_name) : bool
	{
		return trigger_error(
			'@dao : property ' . get_class($object) . '::' . $property_name
			. ' cannot be read alone into a ' . get_class($this) . ' data link',
			E_USER_ERROR
		);
	}

	//-------------------------------------------------------------------------------------- rollback
	/**
	 * Rollback current transaction
	 *
	 * @return ?boolean
	 */
	public function rollback() : ?bool
	{
		return null;
	}

	//---------------------------------------------------------------------------------------- select
	/**
	 * Read selected columns only from data source, using optional filter
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class         class-string<T> class for the read object
	 * @param $properties    string[]|string|Column[] the list of property paths : only those
	 *                       properties will be read. You can use Dao\Func\Column subclasses to get
	 *                       result of functions.
	 * @param $filter_object array|T|null source object for filter, set properties will be used
	 *                       for search. Can be an array associating properties names to matching
	 *                       search value too.
	 * @param $options       Option|Option[] some options for advanced search
	 * @return List_Data a list of read records. Each record values (maybe objects) are stored in
	 *                   the same order as columns.
	 * @template T
	 */
	public function select(
		string $class, array|string $properties, array|object $filter_object = null,
		array|Option $options = []
	) : List_Data
	{
		$class = Builder::className($class);
		if (!is_array($options)) {
			$options = $options ? [$options] : [];
		}
		if (!is_array($properties)) {
			$properties = $properties ? [$properties] : [];
		}
		[$double_pass, $list] = $this->selectOptions($options, $properties);
		if (!isset($list)) {
			$list = $this->selectList($class, $properties);
		}

		if ($double_pass) {
			$new_filter_object = $this->selectFirstPass($class, $properties, $filter_object, $options);
			if (!$new_filter_object) {
				return $list;
			}
			$filter_object = $filter_object
				? Func::andOp([$filter_object, $new_filter_object])
				: $new_filter_object;
		}

		$select     = new Select($class, $properties, $this);
		$query      = $select->prepareQuery($filter_object, $options);
		$result_set = $this->query($query);
		$select->doneQuery();
		if ($options && !$double_pass) {
			$this->getRowsCount('SELECT', $options, $result_set);
		}
		/** @noinspection PhpUnhandledExceptionInspection User exceptions not managed here */
		return $select->fetchResultRows($result_set, $list);
	}

	//------------------------------------------------------------------------------- selectFirstPass
	/**
	 * @param $object_class  string class for the read object
	 * @param $properties    string[]|Column[] the list of property paths : only those properties will
	 *                       be read. You can use Dao\Func\Column subclasses to get result of
	 *                       functions.
	 * @param $filter_object array|object source object for filter, set properties will be used for
	 *                       search. Can be an array associating properties names to matching search
	 *                       value too.
	 * @param $options       Option[] some options for advanced search
	 * @return ?Where
	 */
	private function selectFirstPass(
		string $object_class, array $properties, array|object $filter_object, array &$options
	) : ?Where
	{
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
	 *                       be read. You can use Dao\Func\Column subclasses to get result of
	 *                       functions.
	 * @param $options       Option[] some options for advanced search
	 * @return string[] path of the properties we keep for the first pass, for correct rows counting
	 */
	private function selectFirstPassProperties(
		string $object_class, array $properties, array $options
	) : array
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
				if (str_ends_with($path, ')')) {
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
	 * @param $object_class string class for the read object
	 * @param $columns      string[]|Column[] the list of the columns names : only those properties
	 *                      will be read. You can use 'column.sub_column' to get values from linked
	 *                      objects from the same data source. You can use Dao\Func\Column sub-classes
	 *                      to get result of functions.
	 * @return List_Data
	 */
	private function selectList(string $object_class, array $columns) : List_Data
	{
		$functions  = [];
		$properties = [];
		foreach ($columns as $key => $column) {
			$property_path = is_object($column) ? $key : $column;
			if (Expressions::isFunction($property_path)) {
				$expression    = Expressions::$current->cache[$property_path];
				$property_path = $expression->property_path;
			}
			try {
				$properties[$property_path] = new Reflection_Property($object_class, $property_path);
			}
			catch (ReflectionException) {
				// nothing : no property, period
			}
			$functions[$property_path]  = ($column instanceof Dao_Function) ? $column : null;
		}
		return new Default_List_Data($object_class, $properties, $functions);
	}

	//--------------------------------------------------------------------------------- selectOptions
	/**
	 * @param $options Option[]|callable[] some options for advanced search
	 * @param $columns string[]|Column[] the list of the columns names : only those properties will be
	 *                 read. You can use 'column.sub_column' to get values from linked objects from
	 *                 the same data source. You can use Dao\Func\Column subclasses to get result of
	 *                 functions.
	 * @return array [boolean $double_pass, array $list]
	 */
	private function selectOptions(array $options, array $columns) : array
	{
		$double_pass = false;
		$list        = null;
		foreach ($options as $option) {
			if ($option instanceof Option\Double_Pass) {
				foreach ($columns as $column_key => $column) {
					if (is_object($column) && is_string($column_key)) {
						$column = $column_key;
					}
					if (is_string($column) && str_contains($column, DOT)) {
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
	 * @param $class object|string
	 * @return string
	 */
	public function storeNameOf(object|string $class) : string
	{
		$class_name = is_string($class)
			? $class
			: (($class instanceof Reflection_Class) ? $class->getName() : get_class($class));
		if (!isset($this->tables[$class_name])) {
			$this->tables[$class_name] = parent::storeNameOf($class);
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
	public function truncate(string $class_name) : void
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
	public function writeProperty(object $object, string $property_name, mixed $value = null) : void
	{
		trigger_error(
			'@dao : property ' . get_class($object) . '::' . $property_name
				. ' cannot be written alone into a ' . get_class($this) . ' data link',
			E_USER_ERROR
		);
	}

}
