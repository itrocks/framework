<?php
namespace SAF\Framework\Dao\Func;

use SAF\Framework\Sql\Builder;
use SAF\Framework\Sql\Value;

/**
 * A Dao select function applies only to returned columns : it changes the value of a column
 */
abstract class Column implements Dao_Function
{

	//-------------------------------------------------------------------------------------- aliasSql
	/**
	 * Gets the sql code for the SQL aliasing
	 *
	 * @param $builder       Builder\Columns
	 * @param $property_path string
	 * @return string @example ' AS `alias_name`' or empty string if alias resolving is "off"
	 */
	protected function aliasSql(Builder\Columns $builder, $property_path)
	{
		return $builder->resolve_aliases ? (' AS ' . BQ . $property_path . BQ) : '';
	}

	//-------------------------------------------------------------------------------------- quickSql
	/**
	 * Use this to quickly convert your function to sql without having to do complicated code
	 *
	 * @param $builder       Builder\Columns
	 * @param $property_path string
	 * @param $sql_function  string
	 * @param $args          mixed[]
	 * @return string
	 */
	protected function quickSql(
		Builder\Columns $builder, $property_path, $sql_function, $args = []
	) {
		$sql = $sql_function . '(' . $builder->buildColumn($property_path, false);
		foreach ($args as $arg) {
			$sql .= ', ' . Value::escape($arg);
		}
		return $sql . ')' . $this->aliasSql($builder, $property_path);
	}

	//----------------------------------------------------------------------------------------- toSql
	/**
	 * Returns the Dao function as SQL
	 *
	 * @param $builder       Builder\Columns the sql query builder
	 * @param $property_path string the property path
	 * @return string
	 */
	abstract public function toSql(Builder\Columns $builder, $property_path);

}
