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

	//-------------------------------------------------------------------------------- $transactional
	/**
	 * Is the SQL database engine transactional ?
	 *
	 * @var boolean
	 */
	protected $transactional = false;

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
	 * @param  string $query
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
	 * @param mixed  $result_set The result set : in most cases, will come from executeQuery()
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
	 * @param  string $query
	 * @return integer
	 */
	public abstract function query($query);

	//-------------------------------------------------------------------------------------- rollback
	public function rollback() {}

	//---------------------------------------------------------------------------------------- select
	public function select($object_class, $columns, $filter_object = null)
	{
		if ($filter_object) {
			$filter_map["id_" . strtolower(get_class($filter_object))]
				= $this->getObjectIdentifier($filter_object);
		}
		$query = Sql_Builder::buildSelect($object_class, $columns, $this);
		if ($filter_object) {
			$query .= Sql_Builder::builderWhere($filter_map);
		}
		return $this->selectCore($query, count($columns));
	}

	//---------------------------------------------------------------------------------------- select
	/**
	 * This is the core of the select() call
	 *
	 * @todo factorize
	 * @param string $query
	 * @param string $list_length
	 * @return string 
	 */
	private function selectCore($query, $list_length)
	{
		$list = array();
		$result_set = $this->executeQuery($query);
		$column_count = $this->getColumnCount($result_set);
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
		while ($result = $this->nextRow($result_set)) {
			for ($i = 0; $i < $column_count; $i++) {
				$j = $itoj[$i];
				if (!is_object($classes[$j])) {
					$row[$j] = $result[$i];
				}
				else {
					if (!is_object($row[$j])) {
						// TODO try to get the object from an object map (avoid several instances of the same)
						$row[$j] = Instantiator::newInstance($classes[$j]);
						if ($first) {
							$class = Reflection_Class::getInstanceOf($classes[$j]);
							$properties[$classes[$j]] = $class->accessProperties();
						}
					}
					if ($column_names[$i] === "id") {
						$this->setObjectIdentifier($row[$j], $result[$i]);
					}
					else {
						$object->$column_names[$i] = $result[$i];
					}
				}
			}
			$list[] = row;
			$first = false;
		}
		foreach (array_keys($properties) as $class) if ($class) {
			$class->accessPropertiesDone();
		}
		return $list;
	}

}
