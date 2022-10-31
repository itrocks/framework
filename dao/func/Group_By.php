<?php
namespace ITRocks\Framework\Dao\Func;

use ITRocks\Framework\Sql\Builder\With_Build_Column;

/**
 * Group By function
 */
class Group_By extends Column
{

	//-------------------------------------------------------------------------- values for $function
	const AVERAGE = 'AVG';
	const COUNT   = 'COUNT';
	const MAX     = 'MAX';
	const MIN     = 'MIN';
	const SUM     = 'SUM';

	//------------------------------------------------------------------------------------- $function
	/**
	 * @var string
	 */
	private string $function;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $function string|null
	 */
	public function __construct(string $function = null)
	{
		if (isset($function)) $this->function = $function;
	}

	//----------------------------------------------------------------------------------------- toSql
	/**
	 * Returns the Dao function as SQL
	 *
	 * @param $builder       With_Build_Column the sql query builder
	 * @param $property_path string sql name of the column
	 * @return string
	 */
	public function toSql(With_Build_Column $builder, string $property_path) : string
	{
		return $this->quickSql($builder, $property_path, $this->function);
	}

}
