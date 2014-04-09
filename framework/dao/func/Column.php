<?php
namespace SAF\Framework\Dao\Func;

use SAF\Framework\Sql\Builder\Columns;
use SAF\Framework\Sql\Value;

/**
 * A Dao select function applies only to returned columns : it changes the value of a column
 */
abstract class Column implements Dao_Function
{

	//----------------------------------------------------------------------------------------- toSql
	/**
	 * Returns the Dao function as SQL
	 *
	 * @param $builder       Columns the sql query builder
	 * @param $property_path string the property path
	 * @return string
	 */
	abstract public function toSql(Columns $builder, $property_path);

	//-------------------------------------------------------------------------------------- quickSql
	/**
	 * Use this to quickly convert your function to sql without having to do complicated code
	 *
	 * @param $builder       Columns
	 * @param $property_path string
	 * @param $sql_function  string
	 * @param $args          mixed[]
	 * @return string
	 */
	protected function quickSql(
		Columns $builder, $property_path, $sql_function, $args = []
	) {
		$sql = $sql_function . '(' . $builder->buildColumn($property_path);
		foreach ($args as $arg) {
			$sql .= ', ' . Value::escape($arg);
		}
		return $sql . ') AS ' . BQ . $property_path . BQ;
	}

}
