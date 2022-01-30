<?php
namespace ITRocks\Framework\Sql\Builder;

use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Dao\Func\Column;
use ITRocks\Framework\Dao\Option;
use ITRocks\Framework\Dao\Option\Pre_Load;
use ITRocks\Framework\Dao\Sql\Link;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Sql\Builder;
use ITRocks\Framework\Sql\Join\Joins;

/**
 * The SQL select queries builder
 */
class Select
{
	use Has_Joins;

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string
	 */
	private $class_name;

	//------------------------------------------------------------------------------ $columns_builder
	/**
	 * @var Columns
	 */
	private $columns_builder;

	//-------------------------------------------------------------------------------------- $options
	/**
	 * @var Option[]
	 */
	private $options;

	//--------------------------------------------------------------------------------------- $select
	/**
	 * @var string[]
	 */
	private $select;

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
	 * @param $class_name  string base object class name
	 * @param $properties  string[]|Column[]|null properties paths list
	 *                     (default : all table columns will be read)
	 * @param $where_array array|object where array expression, keys are columns names,
	 *                     or filter object
	 * @param $sql_link    Link
	 * @param $options     Option|Option[] DAO options can be used for complex queries building
	 */
	public function __construct(
		$class_name, array $properties = null, $where_array = null, Link $sql_link = null, $options = []
	) {
		if (!is_array($options)) {
			$options = $options ? [$options] : [];
		}
		$this->joins = $joins = new Joins($class_name, [], strval(Link_Property_Name::in($options)));

		if ($pre_load = Pre_Load::in($options)) {
			if (!$properties) {
				$properties = ['.'];
			}
			$properties = array_merge($properties, $pre_load->properties);
		}

		$this->class_name      = $class_name;
		$this->columns_builder = new Columns($class_name, $properties, $joins);
		$this->tables_builder  = new Tables($class_name, $joins);
		$this->where_builder   = new Where($class_name, $where_array, $sql_link, $joins);
		$this->options         = $options;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return $this->buildQuery();
	}

	//---------------------------------------------------------------------------------- buildOptions
	/**
	 * Builds optional SQL expressions, component of the SELECT query
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return string[]
	 */
	private function buildOptions()
	{
		$options      = [];
		$this->select = [];
		if ($translate = Option\Translate::in($this->options)) {
			$this->columns_builder->translate = [];
		}
		foreach ($this->options as $option) {
			if ($option instanceof Option\Count) {
				$this->select[20] = SP . 'SQL_CALC_FOUND_ROWS';
			}
			elseif ($option instanceof Option\Distinct) {
				$this->select[30] = SP . 'DISTINCT';
			}
			elseif ($option instanceof Option\Group_By) {
				$columns = new Columns($this->class_name, $option->properties ?: ['id'], $this->joins);
				$columns->expand_objects  = false;
				$columns->resolve_aliases = false;
				$group_by                 = $columns->build();
				$options[10] = LF . 'GROUP BY ' . $group_by;
			}
			elseif ($option instanceof Option\Having) {
				$having = new Where(
					$this->class_name, $option->conditions, $this->getSqlLink(), $this->joins
				);
				$having->keyword = 'HAVING';
				$options[11]     = $having->build();
				$this->columns_builder->null_columns = array_merge(
					$this->columns_builder->null_columns, $having->built_columns
				);
			}
			elseif ($option instanceof Option\Limit) {
				// todo this works only with Mysql so beware, this should be into Mysql or something
				$options[30] = LF . 'LIMIT '
					. (isset($option->from) ? ($option->from - 1) . ', ' : '')
					. $option->count;
			}
			elseif ($option instanceof Option\Sort) {
				$columns = new Columns(
					$this->class_name,
					$option->getColumns($this->class_name),
					$this->joins,
					['DESC' => $option->reverse]
				);
				$columns->translate = $this->columns_builder->translate;
				$columns->replaceProperties($this->columns_builder);
				$columns->expand_objects  = false;
				$columns->resolve_aliases = false;
				$order_by                 = $columns->build();
				/** @noinspection PhpUnhandledExceptionInspection */
				if (!$order_by && (new Reflection_Class($this->class_name))->isAbstract()) {
					$order_by = BQ . 'representative' . BQ;
				}
				if ($order_by) {
					$options[20] = LF . 'ORDER BY ' . $order_by;
				}
			}
			elseif (($option instanceof Option\Time_Limit) && $option->time_limit) {
				$this->select[10] = SP . $option->getSql();
			}
		}

		ksort($this->select);
		ksort($options);
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
		// Call of buildOptions() and buildWhere() before buildColumns(), as all joins must be done to
		// correctly deal with all properties.
		// Call of buildColumns() and buildWhere() before buildTables(), to get joins ready.
		// Notice : true was commented as it very often crashes mysql maintainer
		$where   = $this->where_builder->build(/*true*/);
		$options = $this->buildOptions();
		$columns = $this->columns_builder->build();
		$tables  = $this->tables_builder->build();
		return $this->finalize($columns, $where, $tables, $options);
	}

	//-------------------------------------------------------------------------------------- finalize
	/**
	 * Finalize SQL query
	 *
	 * @param $columns string columns list, separated by ', '
	 * @param $tables  string tables list, including joins, without 'FROM'
	 * @param $where   string|string[] where clause, including ' WHERE ' or empty if no filter on read
	 * @param $options string[]
	 * @return string
	 */
	private function finalize($columns, $where, $tables, array $options)
	{
		if (is_array($where)) {
			$sql            = '';
			$options_inside = [];
			$time_limit_sql = '';
			foreach ($options as $option) {
				if (
					(substr($option, 0, 10) !== (LF . 'ORDER BY '))
					&& (substr($option, 0, 7) !== (LF . 'LIMIT '))
				) {
					$options_inside[] = $option;
				}
			}
			foreach ($this->options as $option){
				if ($option instanceof Option\Time_Limit){
					$time_limit_sql = SP . $option->getSql();
					break;
				}
			}
			foreach ($where as $sub_where) {
				if (!empty($sql)) {
					$sql .= LF . 'UNION' . LF;
				}
				$sql .= $this->finalize($columns, $sub_where, $tables, $options_inside);
			}
			$alias = $this->joins->rootAlias();
			$query = Builder::SELECT . $time_limit_sql . SP . '*' . LF
				. 'FROM (' . LF . $sql . LF . ') ' . $alias
				. LF . 'GROUP BY ' . $alias . '.id' . join('', $options);
		}
		else {
			$query =Builder::SELECT . join('', $this->select) . SP . $columns
				. LF . 'FROM' . SP . $tables
				. $where
				. join('', $options);
		}
		return preg_replace('/`id(_\w*)?`/', 'id$1', $query);
	}

	//---------------------------------------------------------------------------------- getClassName
	/**
	 * @return string
	 */
	public function getClassName()
	{
		return $this->class_name;
	}

	//-------------------------------------------------------------------------------------- getJoins
	/**
	 * @return Joins
	 */
	public function getJoins()
	{
		return $this->columns_builder->getJoins();
	}

	//------------------------------------------------------------------------------------ getSqlLink
	/**
	 * @return Link
	 */
	public function getSqlLink()
	{
		return $this->where_builder->getSqlLink();
	}

	//--------------------------------------------------------------------------------- getWhereArray
	/**
	 * @return array|Func\Where|null
	 */
	public function getWhereArray()
	{
		return $this->where_builder->getWhereArray();
	}

	//------------------------------------------------------------------------------- getWhereBuilder
	/**
	 * @return Where
	 */
	public function getWhereBuilder()
	{
		return $this->where_builder;
	}

	//-------------------------------------------------------------------------------------- restrict
	/**
	 * @param $where_array array|object
	 */
	public function restrict($where_array)
	{
		$this->where_builder->restrict($where_array);
	}

}
