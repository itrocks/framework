<?php
namespace SAF\Framework;

/**
 * A Dao select function applies only to returned columns : it changes the value of a column
 */
abstract class Dao_Column_Function implements Dao_Function
{

	//----------------------------------------------------------------------------------------- toSql
	/**
	 * Returns the Dao function as SQL
	 *
	 * @param $builder       Sql_Columns_Builder the sql query builder
	 * @param $property_path string the property path
	 * @return string
	 */
	abstract public function toSql(Sql_Columns_Builder $builder, $property_path);

	//-------------------------------------------------------------------------------------- quickSql
	/**
	 * Use this to quickly convert your function to sql without having to do complicated code
	 *
	 * @param $builder       Sql_Columns_Builder
	 * @param $property_path string
	 * @param $sql_function  string
	 * @param $args          mixed[]
	 * @return string
	 */
	protected function quickSql(
		Sql_Columns_Builder $builder, $property_path, $sql_function, $args = array()
	) {
		$sql = $sql_function . '(' . $builder->buildColumn($property_path);
		foreach ($args as $arg) {
			$sql .= ', ' . Sql_Value::escape($arg);
		}
		return $sql . ') AS `' . $property_path . '`';
	}

}
