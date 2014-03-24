<?php
namespace SAF\Framework;

/**
 * The SQL count queries builder
 *
 * These are 'SELECT COUNT(*) FROM table WHERE expr'
 */
class Sql_Count_Builder
{

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
	 * @param $where_array array|object  where array expression, indices are columns names,
	 * or filter object
	 * @param $sql_link    Sql_Link
	 */
	public function __construct($class_name, $where_array = null, Sql_Link $sql_link = null)
	{
		$joins = new Sql_Joins($class_name);
		$this->tables_builder = new Sql_Tables_Builder($class_name, $joins);
		$this->where_builder  = new Sql_Where_Builder($class_name, $where_array, $sql_link, $joins);
	}

	//------------------------------------------------------------------------------------ buildQuery
	/**
	 * Build and returns the full SQL SELECT query
	 *
	 * @return string
	 */
	public function buildQuery()
	{
		// call buildWhere() before buildTables(), to get joins ready
		$where   = $this->where_builder->build();
		$tables  = $this->tables_builder->build();
		return $this->finalize($where, $tables);
	}

	//-------------------------------------------------------------------------------------- finalize
	/**
	 * Finalize SQL query
	 *
	 * @param $tables  string tables list, including joins, without 'FROM'
	 * @param $where   string where clause, including ' WHERE ' or empty if no filter on read
	 * @return string
	 */
	private function finalize($where, $tables)
	{
		return 'SELECT COUNT(*) FROM ' . $tables . $where;
	}

	//-------------------------------------------------------------------------------------- getJoins
	/**
	 * @return Sql_Joins
	 */
	public function getJoins()
	{
		return $this->where_builder->getJoins();
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
