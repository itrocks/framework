<?php
namespace ITRocks\Framework\Dao\Func;

use ITRocks\Framework\Sql\Builder;
use ITRocks\Framework\Sql\Builder\With_Build_Column;
use ITRocks\Framework\Sql\Value;

/**
 * A Dao select function applies only to returned columns : it changes the value of a read column
 */
abstract class Column implements Dao_Function
{
	use Has_To_String;

	//-------------------------------------------------------------------------------------- aliasSql
	/**
	 * Gets the sql code for the SQL aliasing
	 *
	 * @param $builder       With_Build_Column
	 * @param $property_path string The alias itself
	 * @return string @example ' AS `alias_name`' or empty string if alias resolving is "off"
	 */
	protected function aliasSql(With_Build_Column $builder, string $property_path) : string
	{
		return (($builder instanceof Builder\Columns) && $builder->resolve_aliases)
			? (' AS ' . BQ . $property_path . BQ)
			: '';
	}

	//-------------------------------------------------------------------------------------- quickSql
	/**
	 * Use this to quickly convert your function to sql without having to do complicated code
	 *
	 * @param $builder       With_Build_Column
	 * @param $property_path string
	 * @param $sql_function  string
	 * @param $args          array
	 * @return string
	 */
	protected function quickSql(
		With_Build_Column $builder, string $property_path, string $sql_function, array $args = []
	) : string
	{
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
	 * @param $builder       With_Build_Column the sql query builder
	 * @param $property_path string the property path
	 * @return string
	 */
	abstract public function toSql(With_Build_Column $builder, string $property_path) : string;

}
