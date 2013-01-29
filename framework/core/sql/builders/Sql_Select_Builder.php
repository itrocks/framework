<?php
namespace SAF\Framework;

class Sql_Select_Builder
{
	use Sql_Columns_Builder, Sql_Where_Builder {
		Sql_Columns_Builder::getClasses      insteadof Sql_Where_Builder;
		Sql_Columns_Builder::getClassNames   insteadof Sql_Where_Builder;
		Sql_Columns_Builder::getLinkedTables insteadof Sql_Where_Builder;
	}

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
		$this->constructSqlColumnsBuilder($class, $properties);
		$this->constructSqlWhereBuilder($class, $where_array, $sql_link);
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
		$where   = $this->buildWhere();
		$columns = $this->buildColumns();
		$tables  = $this->buildTables();
		return "SELECT " . $columns . " FROM " . $tables . $where;
	}

}
