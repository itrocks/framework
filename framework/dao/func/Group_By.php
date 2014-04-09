<?php
namespace SAF\Framework\Dao\Func;

use SAF\Framework\Sql\Builder\Columns;

/**
 * Group By function
 */
class Group_By extends Column
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
	 * @param $builder       Columns the sql query builder
	 * @param $property_path string sql name of the column
	 * @return string
	 */
	public function toSql(Columns $builder, $property_path)
	{
		return $this->quickSql($builder, $property_path, $this->function);
	}

}
