<?php
namespace ITRocks\Framework\Dao\Func;

use ITRocks\Framework\Sql\Builder;
use ITRocks\Framework\Widget\List_\Summary_Builder;

/**
 * A Dao where function applies only to conditions : it changes the condition behaviour
 */
interface Where extends Dao_Function
{

	//--------------------------------------------------------------------------------------- toHuman
	/**
	 * Returns the Dao function as Human readable string
	 *
	 * @param $builder       Summary_Builder the sql query builder
	 * @param $property_path string the property path
	 * @param $prefix        string column name prefix
	 * @return string
	 */
	public function toHuman(Summary_Builder $builder, $property_path, $prefix = '');

	//----------------------------------------------------------------------------------------- toSql
	/**
	 * Returns the Dao function as SQL
	 *
	 * @param $builder       Builder\Where the sql query builder
	 * @param $property_path string the property path
	 * @param $prefix        string column name prefix
	 * @return string
	 */
	public function toSql(Builder\Where $builder, $property_path, $prefix = '');

}
