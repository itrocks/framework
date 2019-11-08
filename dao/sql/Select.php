<?php
namespace ITRocks\Framework\Dao\Sql;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func\Dao_Function;
use ITRocks\Framework\Dao\Option;
use ITRocks\Framework\Mapper\Object_Builder_Array;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Reflection\Reflection_Property_Value;
use ITRocks\Framework\Sql;
use ITRocks\Framework\Tools\List_Data;

/**
 * Manages Select() Dao Link calls : how to call and parse the query
 *
 * This is an internal class used by Link, but can be used separately to parse query() result sets
 * into result an array of rows or List_Data
 *
 * @example Minimal example : use current Data_Link, returns an array of rows
 * $select = new Select($class_name, $columns);
 * return $select->fetchResultRows(Dao::query($select->prepareQuery()));
 * @example Compact example that matches the minimal example
 * return Select::executeClassColumns($class_name, $columns);
 * @example Compact example starting from a query and returning an array of rows
 * return Select::executeQuery($query);
 * @example Full-featured SELECT query with options and filter objects (see Link::select())
 * // needs $data_link, $class_name, $columns, $filter_object, $options ; returns a List_Data
 * $list = new Default_List_Data($class_name, $columns);
 * $select = new Select($class_name, $columns, $data_link);
 * $query = $select->prepareQuery($filter_object, $options);
 * $result_set = $data_link->query($query);
 * if (isset($options)) {
 *   $this->getRowsCount('SELECT', $options, $result_set);
 * }
 * return $select->fetchResultRows($result_set, $list);
 * @example Full-featured SELECT query that returns an object[]
 * // needs $data_link, $class_name, $columns, $filter_object, $options ; returns an object[]
 * $select = new Select($class_name, $columns, $data_link);
 * $query = $select->prepareQuery($filter_object, $options);
 * $result_set = $data_link->query($query);
 * if (isset($options)) {
 *   $this->getRowsCount('SELECT', $options, $result_set);
 * }
 * return $select->fetchResultRows($result_set);
 */
class Select
{

	//------------------------------------------------------------------------------------- $callback
	/**
	 * If set, the callback will be called instead of storing into an array or List_Data
	 * Set by fetchResultRows
	 *
	 * @var callable
	 */
	private $callback;

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * Set by __construct()
	 *
	 * @var string
	 */
	private $class_name;

	//-------------------------------------------------------------------------------------- $classes
	/**
	 * Set by prepareFetch()
	 *
	 * @var string[]
	 */
	private $classes;

	//--------------------------------------------------------------------------------- $column_count
	/**
	 * Set by prepareFetch()
	 *
	 * @var integer
	 */
	private $column_count;

	//--------------------------------------------------------------------------------- $column_names
	/**
	 * Set by prepareFetch()
	 *
	 * @var string[]
	 */
	private $column_names;

	//-------------------------------------------------------------------------------------- $columns
	/**
	 * Set by __construct()
	 * Changed when fetchResult() is called (text keys become values)
	 * So : do never call prepareQuery() after fetchResultRows() !
	 *
	 * @var string[]
	 */
	private $columns = [];

	//--------------------------------------------------------------------------------------- $i_to_j
	/**
	 * Set by prepareFetch()
	 *
	 * @var integer[]
	 */
	private $i_to_j;

	//-------------------------------------------------------------------- $ignore_unknown_properties
	/**
	 * If false, fetch will generate an error if the array contains data for properties that do not
	 * exist in object's class.
	 * With true, you do not generate this error but we ignore unknown properties
	 * With null, we store unknown properties into the object
	 *
	 * @var boolean|null
	 */
	public $ignore_unknown_properties = false;

	//------------------------------------------------------------------------------------------ $key
	/**
	 * Key property names
	 * Set by executeQuery()
	 *
	 * @var string[]
	 */
	private $key = ['id'];

	//----------------------------------------------------------------------------------------- $link
	/**
	 * Set by __construct()
	 *
	 * @var Link
	 */
	private $link;

	//------------------------------------------------------------------------------- $object_builder
	/**
	 * Set at start of doFetch() if $class_name is set and $data_store is not a List_Data
	 *
	 * @var Object_Builder_Array
	 */
	private $object_builder;

	//--------------------------------------------------------------------------------- $path_classes
	/**
	 * Key is the property path, value is the associated class name when property type is a class
	 * Set by prepareQuery()
	 *
	 * @var string[]
	 */
	private $path_classes;

	//--------------------------------------------------------------------------- $reflection_classes
	/**
	 * Set by doFetch() and resultToRow()
	 * Reflection classes cache : key is the name of the class, value is the reflection class object
	 *
	 * @var Reflection_Class[]
	 */
	private $reflection_classes = [];

	//----------------------------------------------------------------------------------- $result_set
	/**
	 * Set by prepareFetch()
	 *
	 * @var mixed
	 */
	private $result_set;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Constructs a new Select object, read to use, with all its context data
	 *
	 * @param $class_name string The name of the main business class to start from
	 * @param $columns    string[]|Column[] If not set, the columns names will be taken from the
	 *                    query result
	 * @param $link       Link If not set, the default link will be Dao::current()
	 */
	public function __construct($class_name = null, array $columns = null, Link $link = null)
	{
		$this->link       = $link ?: Dao::current();
		$this->class_name = $class_name;
		if (isset($columns)) {
			$this->columns   = $columns;
			$this->columns[] = 'id';
		}
	}

	//------------------------------------------------------------------------------------ doCallback
	/**
	 * @param $data_store array[]|object
	 * @return boolean if the call returns false for any stored object, this will stop & return false
	 */
	private function doCallback(&$data_store)
	{
		if (isset($this->callback)) {
			foreach ($data_store as $object) {
				if (call_user_func_array($this->callback, [$object, $this->link]) === false) {
					return false;
				}
			}
			$data_store = [];
		}
		return true;
	}

	//--------------------------------------------------------------------------------------- doFetch
	/**
	 * @param $data_store List_Data|array[]|object[]
	 * @return List_Data|array[]|object[]|null
	 */
	private function doFetch($data_store)
	{
		if ($this->class_name && !($data_store instanceof List_Data)) {
			$this->object_builder = new Object_Builder_Array($this->class_name, false);
			$this->object_builder->ignore_unknown_properties = $this->ignore_unknown_properties;
			$data_store                                      = [];
		}
		$first = true;
		while ($result = $this->link->fetchRow($this->result_set)) {
			unset($result['@null']);
			$row = $this->resultToRow($result, $first);
			if (!$this->store($row, $data_store)) {
				$stop = true;
				break;
			}
			$first = false;
		}
		if (!isset($stop)) {
			$this->doCallback($data_store);
		}
		return $this->callback ? null : $data_store;
	}

	//------------------------------------------------------------------------------------- doneQuery
	/**
	 * You must always call this after having called prepareQuery and executed the query
	 */
	public function doneQuery()
	{
		$this->link->popContext();
	}

	//--------------------------------------------------------------------------- executeClassColumns
	/**
	 * A simple execute() feature to use it quick with minimal options
	 *
	 * @param $data_store List_Data|array[]|object[]|callable
	 * @param $key        string[] Key property names
	 * @return List_Data|array[]|object[]|callable
	 */
	public function executeClassColumns($data_store = null, $key = null)
	{
		$result = $this->executeQuery($this->prepareQuery(), $data_store, $key);
		$this->doneQuery();
		return $result;
	}

	//---------------------------------------------------------------------------------- executeQuery
	/**
	 * A simple execute() feature to use with an already built query
	 * Useful for imports from external SQL data sources
	 *
	 * @param $query      string
	 * @param $data_store List_Data|array[]|object[]|callable
	 * @param $key        string[] Key property names
	 * @return List_Data|array[]|object[]|callable
	 */
	public function executeQuery($query, $data_store = null, array $key = null)
	{
		if (isset($key)) {
			$this->key = $key;
		}
		return $this->fetchResultRows($this->link->query($query), $data_store);
	}

	//------------------------------------------------------------------------------- fetchResultRows
	/**
	 * @param $result_set mixed A Link::query() result set
	 * @param $data_store List_Data|array[]|object[]|callable
	 * @return List_Data|array[]|object[]|null
	 */
	public function fetchResultRows($result_set, $data_store = null)
	{
		if (is_callable($data_store)) {
			$this->callback = $data_store;
			$data_store     = null;
		}
		else {
			$this->callback = null;
		}
		$this->result_set = $result_set;
		$this->columns    = $this->prepareColumns($this->columns);
		$this->prepareFetch($data_store);
		return $this->doFetch($data_store);
	}

	//---------------------------------------------------------------------------- objectToProperties
	/**
	 * Changes an object into an array associating properties and their values
	 * This has specific features and is intended for internal use only :
	 * - If the object has an object identifier, only ['id' => $id] will be set, not others properties
	 * - If $object is an array, it keeps and replaces Reflection_Property_Value element by its value
	 *
	 * @param $object array|object|null if already an array, nothing will be done
	 * @return mixed[] keys are properties paths
	 */
	private function objectToProperties($object)
	{
		if (is_object($object) && !($object instanceof Dao_Function)) {
			$id     = $this->link->getObjectIdentifier($object);
			$object = isset($id) ? ['id' => $id] : get_object_vars($object);
		}
		elseif (is_array($object)) {
			foreach ($object as $path => $value) {
				if ($value instanceof Reflection_Property_Value) {
					$object[$path] = $value->value();
				}
			}
		}
		return $object;
	}

	//-------------------------------------------------------------------------------- prepareColumns
	/**
	 * Prepare columns. Add the sql identifier column named 'id', and for each element :
	 * - if the key is a string : the column value is replaced by this key and the key becomes index
	 * - else the column value is kept
	 *
	 * @param $columns string[] The input list of column names
	 * @return string[] The output list of the column names
	 */
	private function prepareColumns(array $columns = [])
	{
		$cols = [];
		foreach ($columns as $may_be_column => $column) {
			$cols[] = is_string($may_be_column) ? $may_be_column : $column;
		}
		return $cols;
	}

	//---------------------------------------------------------------------------------- prepareFetch
	/**
	 * Prepare fetch of rows : initializes
	 * - $classes
	 * - $column_count
	 * - $column_names
	 *
	 * @param $data_store List_Data|array[]|object[]|null
	 */
	private function prepareFetch($data_store)
	{
		$this->classes      = [];
		$this->column_count = $this->link->getColumnsCount($this->result_set);
		$this->column_names = [];
		$this->i_to_j       = [];
		$classes_index      = [];
		$j                  = 0;
		for ($i = 0; $i < $this->column_count; $i++) {
			$column_name = $this->link->getColumnName($this->result_set, $i);
			if ($column_name[0] === '@') {
				$this->column_count --;
				continue;
			}
			$this->column_names[$i] = $column_name;
			if (strpos($column_name, ':') == false) {
				$this->i_to_j[$i] = $j++;
			}
			else {
				$split = explode(':', $column_name, 2);
				if (!isset($this->path_classes[$split[0]])) {
					$this->preparePathClass($split[0]);
				}
				$this->column_names[$i] = $column_name = $split[1];
				$main_property          = $split[0];
				$his_j = isset($classes_index[$main_property]) ? $classes_index[$main_property] : null;
				if (!isset($his_j)) {
					$his_j                         = $j;
					$this->classes[$his_j]         = $this->path_classes[$main_property];
					$classes_index[$main_property] = $j;
					$this->i_to_j[$i]              = $j++;
				}
				else {
					$this->i_to_j[$i] = $his_j;
				}
			}
			if (($data_store instanceof List_Data) && (substr($column_name, 0, 3) === 'id_')) {
				$this->column_names[$i] = $column_name = substr($column_name, 3);
			}
			if (($column_name[0] !== '@') && !isset($this->columns[$i])) {
				$this->columns[$i] = $column_name;
			}
		}
	}

	//------------------------------------------------------------------------------ preparePathClass
	/**
	 * Prepares path_classes if it is null
	 * Must be called after prepareColumns()
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $property_name string
	 */
	private function preparePathClass($property_name)
	{
		/** @noinspection PhpUnhandledExceptionInspection class and property name must be valid */
		$property   = new Reflection_Property($this->class_name, $property_name);
		$class_name = $property->getType()->getElementTypeAsString();
		$this->path_classes[$property_name] = $class_name;
	}

	//---------------------------------------------------------------------------------- prepareQuery
	/**
	 * Prepare the SQL query
	 *
	 * Beware : You must always call doneQuery after having called prepareQuery and executed the query
	 *
	 * @param $filter_object object|array|false source object for filter, set properties will be used
	 *                       for search. Can be an array associating properties names to matching
	 *                       search value too.
	 *                       Special values : null for no filter, false to get no result.
	 * @param $options       Option|Option[] some options for advanced search
	 * @return string
	 */
	public function prepareQuery($filter_object = null, $options = [])
	{
		$filter_object      = $this->objectToProperties($filter_object);
		$sql_select_builder = new Sql\Builder\Select(
			$this->class_name, $this->columns, $filter_object, $this->link, $options
		);
		$query              = $sql_select_builder->buildQuery();
		$this->path_classes = $sql_select_builder->getJoins()->getClasses();
		$this->link->pushContext(array_merge(
			$sql_select_builder->getJoins()->getClassNames(),
			$sql_select_builder->getJoins()->getLinkedTables()
		));
		return $query;
	}

	//----------------------------------------------------------------------------------- resultToRow
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $result array
	 * @param $first  boolean
	 * @return array
	 */
	private function resultToRow(array $result, $first)
	{
		$row = [];
		for ($i = 0; $i < $this->column_count; $i++) {
			$j = $this->i_to_j[$i];
			if (!isset($this->classes[$j])) {
				$row[$this->columns[$j]] = $result[$i];
			}
			else {
				if (!isset($row[$this->columns[$j]])) {
					// TODO LOW try to get the object from object map to avoid multiple instances
					/** @noinspection PhpUnhandledExceptionInspection code classes must be valid */
					$row[$this->columns[$j]] = Builder::create($this->classes[$j]);
					if ($first && !isset($this->reflection_classes[$this->classes[$j]])) {
						/** @noinspection PhpUnhandledExceptionInspection code classes must be valid */
						$class = new Reflection_Class($this->classes[$j]);
						$class->accessProperties();
						$this->reflection_classes[$this->classes[$j]] = $class;
					}
				}
				$property_name = $this->column_names[$i];
				if ($property_name === 'id') {
					$this->link->setObjectIdentifier($row[$this->columns[$j]], $result[$i]);
				}
				else {
					$row[$this->columns[$j]]->$property_name = $result[$i];
				}
			}
		}
		return $row;
	}

	//----------------------------------------------------------------------------------------- store
	/**
	 * Store the row into the data store
	 *
	 * @param $row        array
	 * @param $data_store List_Data|array[]|object[]
	 * @return boolean false if the callback returned false to stop the read process
	 */
	private function store(array $row, &$data_store)
	{
		$result = true;
		if ($data_store instanceof List_Data) {
			$id = array_pop($row);
			$data_store->add($data_store->newRow($this->class_name, $id, $row));
		}
		else {
			// calculate index
			$index = [];
			foreach ($this->key as $key) {
				if (isset($row[$key])) {
					$index[] = $row[$key];
				}
			}
			$index = join(DOT, $index);
			// store
			if (isset($this->class_name)) {
				if ($index !== '') {
					if (isset($data_store[$index])) {
						$data_store[$index] = $this->object_builder->build($row, $data_store[$index]);
					}
					else {
						$result             = $this->doCallback($data_store);
						$data_store[$index] = $this->object_builder->build($row);
					}
				}
				else {
					$result       = $this->doCallback($data_store);
					$data_store[] = $this->object_builder->build($row);
				}
			}
			elseif ($index !== '') {
				$result             = $this->doCallback($data_store);
				$data_store[$index] = $row;
			}
			else {
				$result       = $this->doCallback($data_store);
				$data_store[] = $row;
			}
		}
		return $result;
	}

}
