<?php
namespace SAF\Framework;

/**
 * The SQL select queries builder
 */
class Sql_Select_Builder
{

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string
	 */
	private $class_name;

	//------------------------------------------------------------------------------ $columns_builder
	/**
	 * @var Sql_Columns_Builder
	 */
	private $columns_builder;

	//---------------------------------------------------------------------------------------- $joins
	/**
	 * @var Sql_Joins
	 */
	private $joins;

	//-------------------------------------------------------------------------------------- $options
	/**
	 * @var Dao_Option[]
	 */
	private $options;

	//------------------------------------------------------------------------------- $tables_builder
	/**
	 * @var Sql_Tables_Builder
	 */
	private $tables_builder;

	//-------------------------------------------------------------------------------- $where_builder
	/**
	 * @var Sql_Where_Builder
	 */
	private $where_builder;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Construct a SQL SELECT query
	 *
	 * Supported columns naming forms are :
	 * - column_name : column_name must correspond to a property of class,
	 * - column.foreign_column : column must be a property of class, foreign_column must be a property
	 * of column's type class.
	 *
	 * @param $class_name  string        base object class name
	 * @param $properties  string[]|null properties paths list
	 * (default : all table columns will be read)
	 * @param $where_array array|object  where array expression, indices are columns names,
	 * or filter object
	 * @param $sql_link    Sql_Link
	 * @param $options     Dao_Option|Dao_Option[] DAO options can be used for complex queries building
	 */
	public function __construct(
		$class_name, $properties = null, $where_array = null, Sql_Link $sql_link = null, $options = null
	) {
		$this->joins = $joins = new Sql_Joins($class_name);
		$this->class_name = $class_name;
		$this->columns_builder = new Sql_Columns_Builder($class_name, $properties, $joins);
		$this->tables_builder  = new Sql_Tables_Builder($class_name, $joins);
		$this->where_builder   = new Sql_Where_Builder($class_name, $where_array, $sql_link, $joins);
		$this->options = isset($options) ? (is_array($options) ? $options : array($options)) : array();
	}

	//---------------------------------------------------------------------------------- buildOptions
	/**
	 * Builds optionnal SQL expressions, component of the SELECT query
	 *
	 * @return string[]
	 */
	private function buildOptions()
	{
		$options = array();
		foreach ($this->options as $option) {
			if ($option instanceof Dao_Sort_Option) {
				$order_by = (new Sql_Columns_Builder(
					$this->class_name,
					$option->getColumns($this->class_name),
					$this->joins,
					array("DESC" => $option->reverse)
				))->build();
				if ($order_by) {
					$options[] = " ORDER BY " . $order_by;
				}
			}
		}
		return $options;
	}

	//------------------------------------------------------------------------------------ buildQuery
	/**
	 * Build and returns the full SQL SELECT query
	 *
	 * @return string
	 */
	public function buildQuery()
	{
		// Call of buildWhere() before buildColumns(), as all joins must be done to correctly deal with
		// all properties.
		// Call of buildColumns() and buildWhere() before buildTables(), to get joins ready.
		$where   = $this->where_builder->build();
		$columns = $this->columns_builder->build();
		$tables  = $this->tables_builder->build();
		$options = $this->buildOptions();
		return $this->finalize($columns, $where, $tables, $options);
	}

	//-------------------------------------------------------------------------------------- finalize
	/**
	 * Finalize SQL query
	 *
	 * @param $columns string columns list, separated by ", "
	 * @param $tables  string tables list, including joins, without "FROM"
	 * @param $where   string where clause, including " WHERE " or empty if no filter on read
	 * @param $options string[]
	 * @return string
	 */
	private function finalize($columns, $where, $tables, $options)
	{
		return "SELECT " . $columns . " FROM " . $tables . $where . join("", $options);
	}

	//-------------------------------------------------------------------------------------- getJoins
	/**
	 * @return Sql_Joins
	 */
	public function getJoins()
	{
		return $this->columns_builder->getJoins();
	}

	//------------------------------------------------------------------------------------ getSqlLink
	/**
	 * @return Sql_Link
	 */
	public function getSqlLink()
	{
		return $this->where_builder->getSqlLink();
	}

}
