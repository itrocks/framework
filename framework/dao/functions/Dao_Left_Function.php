<?php
namespace SAF\Framework;

/**
 * Dao_Left_Function
 */
class Dao_Left_Function extends Dao_Column_Function
{

	//--------------------------------------------------------------------------------------- $length
	/**
	 * @var integer
	 */
	public $length;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $length integer
	 */
	public function __construct($length)
	{
		$this->length = $length;
	}

	//----------------------------------------------------------------------------------------- toSql
	/**
	 * Returns the Dao function as SQL
	 *
	 * @param $builder       Sql_Columns_Builder the sql data link
	 * @param $property_path string escaped sql, name of the column
	 * @return string
	 */
	public function toSql(Sql_Columns_Builder $builder, $property_path)
	{
		return $this->quickSql($builder, $property_path, 'LEFT', [$this->length]);
	}

}
