<?php
namespace SAF\Framework;

/**
 * Group By functions
 */
class Dao_Group_By_Function extends Dao_Column_Function
{

	const AVERAGE = 'AVG';
	const COUNT   = 'COUNT';
	const MAX     = 'MAX';
	const MIN     = 'MIN';
	const SUM     = 'SUM';

	//------------------------------------------------------------------------------------- $function
	/**
	 * @var string
	 */
	public $function;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $function string
	 */
	public function __construct($function = null)
	{
		if (isset($function)) $this->function = $function;
	}

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
		return $this->quickSql($builder, $property_path, $this->function);
	}

}
