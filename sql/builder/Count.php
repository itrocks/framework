<?php
namespace ITRocks\Framework\Sql\Builder;

use ITRocks\Framework\Dao\Option;
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
	private Tables $tables_builder;

	//-------------------------------------------------------------------------------- $where_builder
	/**
	 * @var Where
	 */
	private Where $where_builder;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Construct a SQL SELECT query
	 *
	 * Supported columns naming forms are :
	 * - column_name : column_name must correspond to a property of class,
	 * - column.foreign_column : column must be a property of class, foreign_column must be a property
	 * of column's type class.
	 *
	 * @param $class_name  string       base object class name
	 * @param $where_array array|object where array expression, keys are columns names,
	 *                                  or filter object
	 * @param $sql_link    Link
	 * @param $options     Option|Option[] DAO options can be used for complex queries building
	 */
	public function __construct(
		string $class_name, array|object $where_array, Link $sql_link, array|Option $options = []
	) {
		if (!is_array($options)) {
			$options = $options ? [$options] : [];
		}
		$joins = new Joins($class_name, [], strval(Link_Property_Name::in($options)));
		$this->tables_builder = new Tables($class_name, $joins);
		$this->where_builder  = new Where($where_array, $sql_link, $joins);
	}

	//------------------------------------------------------------------------------------ buildQuery
	/**
	 * Build and returns the full SQL SELECT query
	 *
	 * @return string
	 */
	public function buildQuery() : string
	{
		// call buildWhere() before buildTables(), to get joins ready
		$where  = $this->where_builder->build(true);
		$tables = $this->tables_builder->build();
		return $this->finalize($where, $tables);
	}

	//-------------------------------------------------------------------------------------- finalize
	/**
	 * Finalize SQL query
	 *
	 * @param $where  string|string[] where clause, including ' WHERE ' or empty if no filter on read
	 * @param $tables string tables list, including joins, without 'FROM'
	 * @return string
	 */
	private function finalize(array|string $where, string $tables) : string
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
	public function getJoins() : Joins
	{
		return $this->where_builder->getJoins();
	}

	//------------------------------------------------------------------------------------ getSqlLink
	/**
	 * @return Link
	 */
	public function getSqlLink() : Link
	{
		return $this->where_builder->getSqlLink();
	}

}
