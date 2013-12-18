<?php
namespace SAF\Framework;

/**
 * Max function
 */
class Dao_Max_Function extends Dao_Column_Function
{

	//----------------------------------------------------------------------------------------- toSql
	/**
	 * Returns the Dao function as SQL
	 *
	 * @param $builder       Sql_Columns_Builder the sql query builder
	 * @param $property_path string sql name of the column
	 * @return string
	 */
	public function toSql(Sql_Columns_Builder $builder, $property_path)
	{
		return $this->quickSql($builder, $property_path, "MAX");
	}

}
