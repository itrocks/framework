<?php
namespace SAF\Framework;

class Sql_Select_Builder
{

	//------------------------------------------------------------------------------ $columns_builder
	/**
	 * @var Sql_Columns_Builder
	 */
	private $columns_builder;

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
	 * column_name : column_name must correspond to a property of class
	 * column.foreign_column : column must be a property of class, foreign_column must be a property of column's type class
	 *
	 * @param $class       string        base object class name
	 * @param $properties  string[]|null properties paths list
	 * @param $where_array array|object  where array expression, indices are columns names, or filter object
	 * @param $sql_link    Sql_Link
	 */
	public function __construct($class, $properties, $where_array = null, Sql_Link $sql_link = null)
	{
		$joins = new Sql_Joins($class);
		$this->columns_builder = new Sql_Columns_Builder($class, $properties, $joins);
		$this->tables_builder  = new Sql_Tables_Builder($class, $joins);
		$this->where_builder   = new Sql_Where_Builder($class, $where_array, $sql_link, $joins);
	}

	//-------------------------------------------------------------------------------------- getQuery
	/**
	 * Build and returns the full SQL SELECT query
	 *
	 * @return string
	 */
	public function buildQuery()
	{
		// call buildWhere() before buildColumns(), as all joins must be done to correctly deal with all properties
		// call buildColumns() and buildWhere() before buildTables(), to get joins ready
		$where   = $this->where_builder->build();
		$columns = $this->columns_builder->build();
		$tables  = $this->tables_builder->build();
		return $this->finalize($columns, $where, $tables);
	}

	//-------------------------------------------------------------------------------------- finalize
	/**
	 * Finalize SQL query
	 *
	 * @param $columns string columns list, separated by ", "
	 * @param $tables  string tables list, including joins, without "FROM"
	 * @param $where   string where clause, including " WHERE " or empty if no filter on read
	 * @return string
	 */
	private function finalize($columns, $where, $tables)
	{
		return "SELECT " . $columns . " FROM " . $tables . $where;
	}

	//-------------------------------------------------------------------------------------- getJoins
	public function getJoins()
	{
		return $this->columns_builder->getJoins();
	}

	//------------------------------------------------------------------------------------ getSqlLink
	public function getSqlLink()
	{
		return $this->where_builder->getSqlLink();
	}

}
