<?php
namespace SAF\Framework\Dao\Func;

use SAF\Framework\Sql\Builder\Columns;

/**
 * A simple function call
 */
class Call extends Column
{

	//------------------------------------------------------------------------------ $function values
	const DISTINCT = 'DISTINCT';
	const TRIM     = 'TRIM';

	//------------------------------------------------------------------------------------- $function
	/**
	 * @var string
	 */
	private $function;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $function      string
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
	 * @param $property_path string the property path
	 * @return string
	 */
	public function toSql(Columns $builder, $property_path)
	{
		return $this->quickSql($builder, $property_path, $this->function);
	}

}
