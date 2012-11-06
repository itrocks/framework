<?php
namespace SAF\Framework;

/**
 * @todo having executeQuery() and query() is perhaps not a good idea
 */
abstract class Sql_Link extends Identifier_Map_Data_Link implements Transactional_Data_Link
{

	//----------------------------------------------------------------------------------- $connection
	/**
	 * Connection to a SQL driver
	 *
	 * @var mixed
	 */
	protected $connection;

	//--------------------------------------------------------------------------------------- $tables
	/**
	 * Links each class name to it's storage table name 
	 *
	 * @var multitype:string indice is the class name, with or without namespace
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
	public function __construct($parameters)
	{
		$this->tables = isset($parameters["tables"]) ? $parameters["tables"] : array();
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
	 * @param string $query
	 * @return mixed  the sql query result set (type and use may depends on each SQL data link) 
	 */
	protected abstract function executeQuery($query);

	//----------------------------------------------------------------------------------------- fetch
	/**
	 * Fetch a result from a result set to an object
	 *
	 * Sql_Link inherited classes must implement fetching result rows only into this method.
	 * If $class_name is null, a stdClass object will be created.
	 *
	 * @param mixed  $result_set The result set : in most cases, will come from executeQuery()
	 * @param string $class_name The class name to store the result data into
	 * @return object
	 */
	protected abstract function fetch($result_set, $class_name = null);

	//------------------------------------------------------------------------------------------ free
	/**
	 * Free a result set
	 *
	 * Sql_Link inherited classes must implement freeing result sets only into this method.
	 *
	 * @param mixed $result_set The result set : in most cases, will come from executeQuery()
	 */
	protected abstract function free($result_set);

	//--------------------------------------------------------------------------------- getColumnName
	/**
	 * Gets the column name from result set
	 *
	 * Sql_Link inherited classes must implement getting column name only into this method.
	 *
	 * @param mixed $result_set The result set : in most cases, will come from executeQuery()
	 * @param mixed $index The index of the column we want to get the SQL name from
	 * @return string
	 */
	protected abstract function getColumnName($result_set, $index);

	//-------------------------------------------------------------------------------- getColumnsCount
	/**
	 * Gets the column count from result set
	 *
	 * Sql_Link inherited classes must implement getting columns count only into this method.
	 *
	 * @param mixed $result_set The result set : in most cases, will come from executeQuery()
	 * @return integer
	 */
	protected abstract function getColumnsCount($result_set);

	//----------------------------------------------------------------------------------------- query
	/**
	 * Executes an SQL query and returns the inserted record identifier (if applyable)
	 *
	 * @param string $query
	 * @return integer
	 */
	public abstract function query($query);

	//-------------------------------------------------------------------------------------- rollback
	public function rollback() {}

	//---------------------------------------------------------------------------------------- select
	/**
	 * @todo factorize
	 */
	public function select($object_class, $columns, $filter_object = null)
	{
		$filter_object = $this->objectToProperties($filter_object);
		$list = new Default_List_Data($object_class, $columns);
		$columns[] = "id";
		$query = Sql_Builder::buildSelect($object_class, $columns, $filter_object, $this);
		$list_length = count($columns);
		$result_set = $this->executeQuery($query);
		$column_count = $this->getColumnsCount($result_set);
		$classes = array();
		$classes_index = array();
		$j = 0;
		for ($i = 0; $i < $column_count; $i++) {
			$column_names[$i] = $this->getColumnName($result_set, $i);
			if (strpos($column_names[$i], ":") == false) {
				$itoj[$i] = $j++;
			}
			else {
				$split = explode("\\:", $column_names[$i]);
				$column_names[$i] = $split[1];
				$object_class = $split[0];
				$hisj = $classes_index[$object_class];
				if (!$hisj) {
					$hisj = $j;
					$classes_index[$object_class] = $j;
					$create_object[$j] = true;
					$itoj[$i] = $j++;
				}
				else {
					$itoj[$i] = $hisj;
				}
				$classes[$hisj] = $object_class;
			}
			if ((count($column_names[$i]) > 3) && substr($column_names[$i], 0, 3) === "id_") {
				$column_names[$i] = substr($column_names[$i], 3);
			}
		}
		$first = true;
		$properties = array();
		while ($result = $this->fetchRow($result_set)) {
			$object = Search_Object::newInstance($object_class);
			for ($i = 0; $i < $column_count; $i++) {
				$j = $itoj[$i];
				if (!isset($classes[$j]) || !is_object($classes[$j])) {
					$row[$columns[$j]] = $result[$i];
				}
				else {
					if (!is_object($row[$columns[$j]])) {
						// TODO try to get the object from an object map (avoid several instances of the same)
						$row[$columns[$j]] = Instantiator::newInstance($classes[$j]);
						if ($first) {
							$class = Reflection_Class::getInstanceOf($classes[$j]);
							$properties[$classes[$j]] = $class->accessProperties();
						}
					}
					if ($column_names[$i] === "id") {
						$this->setObjectIdentifier($row[$columns[$j]], $result[$i]);
					}
					else {
						$object->$column_names[$i] = $result[$i];
					}
				}
			}
			$id = array_pop($row);
			$list->add(new Default_List_Row($object_class, $id, $row));
			$first = false;
		}
		foreach (array_keys($properties) as $class) if ($class) {
			$class->accessPropertiesDone();
		}
		return $list;
	}

	//----------------------------------------------------------------------------------- storeNameOf
	public function storeNameOf($class_name)
	{
		if (isset($this->tables[$class_name])) {
			$store_name = $this->tables[$class_name];
		}
		elseif (isset($this->tables[Namespaces::shortClassName($class_name)])) {
			$store_name = $this->tables[Namespaces::shortClassName($class_name)];
		}
		else {
			$store_name = parent::storeNameOf($class_name);
		}
		return $store_name;
	}

}
