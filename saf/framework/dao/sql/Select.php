<?php
namespace SAF\Framework\Dao\Sql;

//------------------------------------------------------------------------------------------ Select
use SAF\Framework\Builder;
use SAF\Framework\Dao;
use SAF\Framework\Dao\Option;
use SAF\Framework\Mapper\Object_Builder_Array;
use SAF\Framework\Reflection\Reflection_Class;
use SAF\Framework\Reflection\Reflection_Property;
use SAF\Framework\Reflection\Reflection_Property_Value;
use SAF\Framework\Sql;
use SAF\Framework\Tools\List_Data;

/**
 * Manages Select() Dao Link calls : how to call and parse the query
 *
 * This is an internal class used by Link, but can be used separately to parse query() result sets
 * into result an array of rows or Data_List
 *
 * @example Minimal example : use current Data_Link, returns an array of rows
 * $select = new Select($class_name, $columns);
 * return $select->fetchResultRows(Dao::query($select->prepareQuery()));
 *
 * @example Compact example that matches the minimal example
 * return Select::executeClassColumns($class_name, $columns);
 *
 * @example Compact example starting from a query and returning an array of rows
 * return Select::executeQuery($query);
 *
 * @example Full-featured SELECT query with options and filter objects (see Link::select())
 * // needs $data_link, $class_name, $columns, $filter_object, $options ; returns a List_Data
 * $list = new Default_List_Data($class_name, $columns);
 * $select = new Select($class_name, $columns, $data_link);
 * $query = $select->prepareQuery($filter_object, $options);
 * $result_set = $data_link->query($query);
 * if (isset($options)) {
 *   $this->getRowsCount($result_set, 'SELECT', $options);
 * }
 * return $select->fetchResultRows($result_set, $list);
 *
 * @example Full-featured SELECT query that returns an object[]
 * // needs $data_link, $class_name, $columns, $filter_object, $options ; returns an object[]
 * $select = new Select($class_name, $columns, $data_link);
 * $query = $select->prepareQuery($filter_object, $options);
 * $result_set = $data_link->query($query);
 * if (isset($options)) {
 *   $this->getRowsCount($result_set, 'SELECT', $options);
 * }
 * return $select->fetchResultRows($result_set);
 */
class Select
{

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

	//-------------------------------------------------------------------------------------- $columns
	/**
	 * Set by __construct()
	 * Changed when fetchResult() is called (text keys become values)
	 * So : do never call prepareQuery() after fetchResultRows() !
	 *
	 * @var string[]
	 */
	private $columns = null;

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

	//--------------------------------------------------------------------------------------- $i_to_j
	/**
	 * Set by prepareFetch()
	 *
	 * @var integer[]
	 */
	private $i_to_j;

	//----------------------------------------------------------------------------------------- $link
	/**
	 * Set by __construct()
	 *
	 * @var Link
	 */
	private $link;

	//------------------------------------------------------------------------------- $object_builder
	/**
	 * Set at start of doFetch() if $class_name is set and $list is not a Data_List
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
	 * @param $columns    string[] If not set, the columns names will be taken from the query result
	 * @param $link       Link If not set, the default link will be Dao::current()
	 */
	public function __construct($class_name, $columns = null, Link $link = null)
	{
		$this->link       = $link ?: Dao::current();
		$this->class_name = $class_name;
		if (isset($columns)) {
			$this->columns   = $columns;
			$this->columns[] = 'id';
		}
	}

	//--------------------------------------------------------------------------------------- doFetch
	/**
	 * @param $list List_Data
	 * @return List_Data|array[]|object[]
	 */
	private function doFetch(List_Data $list = null)
	{
		if ($this->class_name && !$list) {
			$this->object_builder = new Object_Builder_Array($this->class_name);
			$list = [];
		}
		$first = true;
		while ($result = $this->link->fetchRow($this->result_set)) {
			$row = $this->resultToRow($result, $first);
			$this->store($row, $list);
			$first = false;
		}
		return $list;
	}

	//--------------------------------------------------------------------------- executeClassColumns
	/**
	 * A simple static execute() feature to use it quick with minimal options
	 *
	 * @param $class_name string
	 * @param $columns    string[]
	 * @param $link       Link
	 * @return object[]
	 */
	public static function executeClassColumns($class_name, $columns, Link $link = null)
	{
		$select = new Select($class_name, $columns, $link);
		return $select->fetchResultRows($select->link->query($select->prepareQuery()));
	}

	//----------------------------------------------------------------------------- executeClassQuery
	/**
	 * A simple static execute() feature to use with an already built query
	 * Useful for imports from external SQL data sources
	 *
	 * @param $query      string
	 * @param $class_name string If not set, the returned value is an array[], else each row will be
	 *                           changed into an object (with sub-objects too)
	 * @param $link       Link   Default is Dao::current()
	 * @return array[]|object[]
	 */
	public static function executeQuery($query, $class_name = null, Link $link = null)
	{
		$select = new Select($class_name, null, $link);
		return $select->fetchResultRows($select->link->query($query));
	}

	//------------------------------------------------------------------------------- fetchResultRows
	/**
	 * @param $result_set   mixed A Link::query() result set
	 * @param $list         List_Data
	 * @return List_Data|array[]|object[]
	 */
	public function fetchResultRows($result_set, List_Data $list = null)
	{
		$this->result_set = $result_set;
		$this->columns = $this->prepareColumns($this->columns);
		$this->prepareFetch();
		return $this->doFetch($list);
	}

	//---------------------------------------------------------------------------- objectToProperties
	/**
	 * Changes an object into an array associating properties and their values
	 * This has specific features and is intended for internal use only :
	 * - If the object has an object identifier, only ['id' => $id] will be set, not others properties
	 * - If $object is an array, it keeps and replaces Reflection_Property_Value element by its value
	 *
	 * @param $object array|object|null if already an array, nothing will be done
	 * @return mixed[] indices ar properties paths
	 */
	private function objectToProperties($object)
	{
		if (is_object($object)) {
			$id = $this->link->getObjectIdentifier($object);
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
	private function prepareColumns($columns)
	{
		$cols = [];
		if ($columns) {
			foreach ($columns as $may_be_column => $column) {
				$cols[] = is_string($may_be_column) ? $may_be_column : $column;
			}
		}
		return $cols;
	}

	//---------------------------------------------------------------------------------- prepareFetch
	/**
	 * Prepare fetch of rows : initializes
	 * - $classes
	 * - $column_count
	 * - $column_names
	 */
	private function prepareFetch()
	{
		$this->classes = [];
		$this->column_count = $this->link->getColumnsCount($this->result_set);
		$this->column_names = [];
		$this->i_to_j = [];
		$classes_index = [];
		$j = 0;
		for ($i = 0; $i < $this->column_count; $i++) {
			$this->column_names[$i] = $column_name = $this->link->getColumnName($this->result_set, $i);
			if (strpos($column_name, ':') == false) {
				$this->i_to_j[$i] = $j++;
			}
			else {
				$split = explode(':', $column_name, 2);
				if (!isset($this->path_classes[$split[0]])) {
					$this->preparePathClass($split[0]);
				}
				$this->column_names[$i] = $column_name = $split[1];
				$main_property = $split[0];
				$his_j = isset($classes_index[$main_property]) ? $classes_index[$main_property] : null;
				if (!isset($his_j)) {
					$his_j = $j;
					$this->classes[$his_j] = $this->path_classes[$main_property];
					$classes_index[$main_property] = $j;
					$this->i_to_j[$i] = $j++;
				}
				else {
					$this->i_to_j[$i] = $his_j;
				}
			}
			if (substr($column_name, 0, 3) === 'id_') {
				$this->column_names[$i] = $column_name = substr($column_name, 3);
			}
			if (!isset($this->columns[$i])) {
				$this->columns[$i] = $column_name;
			}
		}
	}

	//------------------------------------------------------------------------------ preparePathClass
	/**
	 * Prepares path_classes if it is null
	 * Must be called after prepareColumns()
	 *
	 * @param $property_name string
	 */
	private function preparePathClass($property_name)
	{
		$property = new Reflection_Property($this->class_name, $property_name);
		$class_name = $property->getType()->getElementTypeAsString();
		$this->path_classes[$property_name] = $class_name;
	}

	//---------------------------------------------------------------------------------- prepareQuery
	/**
	 * @param $filter_object object|array source object for filter, set properties will be used for
	 *                       search. Can be an array associating properties names to corresponding
	 *                       search value too.
	 * @param $options       Option[] some options for advanced search
	 * @return string
	 */
	public function prepareQuery($filter_object = null, $options = [])
	{
		$filter_object = $this->objectToProperties($filter_object);
		$sql_select_builder = new Sql\Builder\Select(
			$this->class_name, $this->columns, $filter_object, $this->link, $options
		);
		$query = $sql_select_builder->buildQuery();
		$this->path_classes = $sql_select_builder->getJoins()->getClasses();
		$this->link->setContext(array_merge(
			$sql_select_builder->getJoins()->getClassNames(),
			$sql_select_builder->getJoins()->getLinkedTables()
		));
		return $query;
	}

	//----------------------------------------------------------------------------------- resultToRow
	/**
	 * @param $result mixed[]
	 * @param $first  boolean
	 * @return array
	 */
	private function resultToRow($result, $first)
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
					$row[$this->columns[$j]] = Builder::create($this->classes[$j]);
					if ($first && !isset($this->reflection_classes[$this->classes[$j]])) {
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

	//----------------------------------------------------------------------------------- rowToObject
	/**
	 * Change a row into an object
	 *
	 * @param $row array The source row
	 * @return object The generated object (sub-objects when 'property.path' key is used)
	 */
	private function rowToObject($row)
	{
		return $this->object_builder->build($row);
	}

	//----------------------------------------------------------------------------------------- store
	/**
	 * Store the row into the list
	 *
	 * @param $row  array
	 * @param $list List_Data|array[]|object[]
	 */
	private function store($row, &$list)
	{
		// store into $list
		if ($list instanceof List_Data) {
			$id = array_pop($row);
			$list->add($list->newRow($this->class_name, $id, $row));
		}
		else {
			$list[] = isset($this->class_name) ? $this->rowToObject($row) : $row;
		}
	}

}
