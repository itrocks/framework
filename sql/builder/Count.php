<?php
namespace ITRocks\Framework\Sql\Builder;

use ITRocks\Framework\Dao\Sql\Link;
use ITRocks\Framework\Sql\Join\Joins;

/**
 * The SQL count queries builder
 *
 * These are 'SELECT COUNT(*) FROM table WHERE expr'
 */
class Count
{

	//------------------------------------------------------------------------------- $tables_builder
	/**
	 * @var Tables
	 */
	private $tables_builder;

	//-------------------------------------------------------------------------------- $where_builder
	/**
	 * @var Where
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
	 * @param $where_array array|object  where array expression, keys are columns names,
	 * or filter object
	 * @param $sql_link    Link
	 */
	public function __construct($class_name, $where_array = null, Link $sql_link = null)
	{
		$joins = new Joins($class_name);
		$this->tables_builder = new Tables($class_name, $joins);
		$this->where_builder  = new Where($class_name, $where_array, $sql_link, $joins);
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
		$where   = $this->where_builder->build(true);
		$tables  = $this->tables_builder->build();
		return $this->finalize($where, $tables);
	}

	//-------------------------------------------------------------------------------------- finalize
	/**
	 * Finalize SQL query
	 *
	 * @param $tables  string tables list, including joins, without 'FROM'
	 * @param $where   string|string[] where clause, including ' WHERE ' or empty if no filter on read
	 * @return string
	 */
	private function finalize($where, $tables)
	{
		if (is_array($where)) {
			$sql = '';
			foreach ($where as $sub_where) {
				if (!empty($sql)) {
					$sql .= LF . 'UNION' . LF;
				}
				$sql .= $this->finalize($sub_where, $tables);
			}
			$alias = $this->getJoins()->rootAlias();
			return 'SELECT COUNT(*)' . LF
				. 'FROM (' . LF . $sql . LF . ') ' . $alias . LF
				. 'GROUP BY ' . $alias . '.id';
		}
		return 'SELECT COUNT(*)' . LF . 'FROM ' . $tables . $where;
	}

	//-------------------------------------------------------------------------------------- getJoins
	/**
	 * @return Joins
	 */
	public function getJoins()
	{
		return $this->where_builder->getJoins();
	}

	//------------------------------------------------------------------------------------ getSqlLink
	/**
	 * @return Link
	 */
	public function getSqlLink()
	{
		return $this->where_builder->getSqlLink();
	}

}
