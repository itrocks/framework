<?php
namespace SAF\Framework;

trait Sql_Columns_Builder
{
	use Sql_Joins_Builder;

	//----------------------------------------------------------------------------------- $properties
	/**
	 * Properties paths list
	 *
	 * @var string[]
	 */
	private $properties;

	//----------------------------------------------------------------------------------- buildColumn
	/**
	 * Build SQL query section for a single column
	 *
	 * @param string $path the past of the matching property
	 * @param boolean $first_property
	 * @return string
	 */
	private function buildColumn($path, &$first_property)
	{
		$sql_columns = "";
		if ($first_property) $first_property = false; else $sql_columns = ", ";
		list($master_path, $column_name) = Sql_Builder::splitPropertyPath($path);
		$join = $this->joins->getJoin($master_path);
		$sql_columns .= $join
			? "$join->foreign_alias.`$column_name` AS `$path`"
			: "t0.`$path` AS `$path`";
		return $sql_columns;
	}

	//---------------------------------------------------------------------------------- buildColumns
	/**
	 * Build the columns list, based on properties paths
	 *
	 * @return string
	 */
	protected function buildColumns()
	{
		if (isset($this->properties)) {
			$sql_columns = "";
			$first_property = true;
			foreach ($this->properties as $path) {
				$join = $this->joins->add($path);
				$sql_columns .= $join
					? $this->buildObjectColumns($path, $join, $first_property)
					: $this->buildColumn($path, $first_property);
			}
		} elseif ($this->joins->getJoins()) {
			// TODO why not read all properties of all tables in order to fill in result set ?
			$sql_columns = "t0.*";
		} else {
			$sql_columns = "*";
		}
		return $sql_columns;
	}

	//---------------------------------------------------------------------------- buildObjectColumns
	/**
	 * Build columns list for an object, in order to instantiate this object when read
	 *
	 * @param string $path
	 * @param Sql_Join $join
	 * @param boolean $first_property
	 * @return string
	 */
	private function buildObjectColumns($path, Sql_Join $join, &$first_property)
	{
		$sql_columns = "";
		foreach ($this->joins->getProperties($path) as $property) {
			$column_name = Sql_Builder::buildColumnName($property);
			if ($column_name) {
				if ($first_property) $first_property = false; else $sql_columns .= ", ";
				$sql_columns .= "$join->foreign_alias.`$column_name` AS `$path:$property->name`";
			}
		}
		if ($first_property) $first_property = false; else $sql_columns .= ", ";
		$sql_columns .= "$join->foreign_alias.id AS `$path:id`";
		return $sql_columns;
	}

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Construct the SQL columns list section of a query
	 *
	 * @param string          $class       root class name
	 * @param string[] $properties properties paths list
	 */
	protected function constructSqlColumnsBuilder($class, $properties)
	{
		$this->joins = new Sql_Joins($class);
		$this->properties = $properties;
	}

}
