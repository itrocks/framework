<?php
namespace ITRocks\Framework\Dao\Func;

use ITRocks\Framework\Sql\Builder\With_Build_Column;

/**
 * A simple function call
 */
class Call extends Column
{

	//------------------------------------------------------------------ Some SQL functions constants
	const DISTINCT = 'DISTINCT';
	const TRIM     = 'TRIM';

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
	 * @param $property_path string the property path
	 * @return string
	 */
	public function toSql(With_Build_Column $builder, string $property_path) : string
	{
		return $this->quickSql($builder, $property_path, $this->function);
	}

}
