<?php
namespace SAF\Framework;

/**
 * A Dao where function applies only to conditions : it changes the condition behavior
 */
interface Dao_Where_Function extends Dao_Function
{

	//----------------------------------------------------------------------------------------- toSql
	/**
	 * Returns the Dao function as SQL
	 *
	 * @param $builder       Sql_Where_Builder the sql query builder
	 * @param $property_path string the property path
	 * @return string
	 */
	public function toSql(Sql_Where_Builder $builder, $property_path);

}
