<?php
namespace ITRocks\Framework\Dao\Func;

use ITRocks\Framework\Sql\Builder\With_Build_Column;

/**
 * Dao Left function
 */
class Left extends Column
{

	//--------------------------------------------------------------------------------------- $length
	/**
	 * @var integer
	 */
	public int $length;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $length integer
	 */
	public function __construct(int $length)
	{
		$this->length = $length;
	}

	//----------------------------------------------------------------------------------------- toSql
	/**
	 * Returns the Dao function as SQL
	 *
	 * @param $builder       With_Build_Column the sql data link
	 * @param $property_path string escaped sql, name of the column
	 * @return string
	 */
	public function toSql(With_Build_Column $builder, string $property_path) : string
	{
		return $this->quickSql($builder, $property_path, 'LEFT', [$this->length]);
	}

}
