<?php
namespace ITRocks\Framework\Dao\Sql;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao\Data_Link\Identifier_Map;
use ITRocks\Framework\Dao\Data_Link\Transactional;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Dao\Func\Column;
use ITRocks\Framework\Dao\Func\Dao_Function;
use ITRocks\Framework\Dao\Option;
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
	public function __construct(array $parameters = [])
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
				return new Default_List_Data($object_class, $properties);
			}
			$filter_object = $filter_object
				? Func::andOp([$filter_object, Func::orOp($new_filter_object)])
				: $new_filter_object;
		}

		$select     = new Select($object_class, $properties, $this);
		$query      = $select->prepareQuery($filter_object, $options);
		$result_set = $this->query($query);
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
	 * @return array An array of read objects identifiers
	 */
	private function selectFirstPass(
		$object_class, array $properties, $filter_object, array &$options
	) {
		// select properties with @link Collection|Map : only if Count
		$select_properties = false;
		foreach ($options as $option) {
			if ($option instanceof Option\Count) {
				$select_properties = true;
				break;
			}
		}
		$properties = $select_properties
			? $this->selectFirstPassProperties($object_class, $properties, $options)
			: [];
		// first pass
		$select            = new Select($object_class, $properties, $this);
		$query             = $select->prepareQuery($filter_object, $options);
		$result_set        = true;
		$read_lines_filter = $this->query($query, AS_VALUES, $result_set);
		if ($options && $result_set) {
			$this->getRowsCount('SELECT', $options, $result_set);
			$this->free($result_set);
			foreach ($options as $key => $option) {
				// keep Limit if we did not need to keep @link Collection|Map property paths for counting
				// if we remove Limit, we may have more result records than we want in this case
				if (($option instanceof Option\Limit) && !$properties) {
					unset($options[$key]);
				}
				// the Count option is kept by the originator, but we don't want it for the second pass
				// only the first pass counts the number of resulting lines
				elseif ($option instanceof Option\Count) {
					unset($options[$key]);
				}
			}
		}
		return $read_lines_filter;
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
		$result = [];
		foreach ($options as $option) {
			if ($option instanceof Option\Group_By) {
				$result = array_merge($properties, $option->properties);
			}
		}
		if (!$result) {
			foreach ($properties as $key => $property_path) {
				if (is_string($key) && !is_string($property_path)) {
					$property_path = $key;
				}
				// $keep_path = the deepest path of a @link Collection|Map property
				$keep_path = $path = '';
				foreach (explode(DOT, $property_path) as $property_name) {
					$path .= ($path ? DOT : '') . $property_name;
					if (substr($path, -1) !== ')') {
						/** @noinspection PhpUnhandledExceptionInspection class and property must be valid */
						$link  = Link_Annotation::of(new Reflection_Property($object_class, $path));
						if ($link->is(Link_Annotation::COLLECTION, Link_Annotation::MAP)) {
							$keep_path = $path;
						}
					}
				}
				if ($keep_path) {
					$add = true;
					// add the path only if is not the start of an already added path
					$property_path_dot = $property_path . DOT;
					$length            = strlen($property_path_dot);
					foreach ($result as $existing_path) {
						if (substr($existing_path, 0, $length) === $property_path_dot) {
							$add = false;
						}
					}
					if ($add) {
						// optimization : get the full path if the last property is not an object
						if (
							((substr_count($property_path, DOT) - substr_count($keep_path, DOT)) === 1)
							&& (isset($link) && !$link->value)
						) {
							$result[$property_path] = $property_path;
						}
						// get the intermediate path (the entire object will be read, but who cares ?)
						else {
							$result[$keep_path] = $keep_path;
						}
						// remove all other already added paths that are the start of the path
						$remove_path = '';
						foreach (explode(DOT, $keep_path) as $part) {
							$remove_path .= ($remove_path ? DOT : '') . $part;
							if (isset($result[$remove_path])) {
								unset($result[$remove_path]);
							}
						}
					}
				}
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
		$properties = [];
		foreach ($columns as $key => $column) {
			$property_path = is_object($column) ? $key : $column;
			/** @noinspection PhpUnhandledExceptionInspection property must be valid */
			$properties[$property_path] = ($column instanceof Dao_Function)
				? $column
				: new Reflection_Property($object_class, $property_path);
		}
		return new Default_List_Data($object_class, $properties);
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
		}
		return [$double_pass, $list];
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
		$this->setContext($class_name);
		$table_name = $this->storeNameOf($class_name);
		$this->query('TRUNCATE TABLE ' . BQ . $table_name . BQ);
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
