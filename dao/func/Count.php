<?php
namespace ITRocks\Framework\Dao\Func;

use ITRocks\Framework\Sql\Builder\With_Build_Column;

/**
 * Count column function
 */
class Count extends Column
{

	//------------------------------------------------------------------------------------- $distinct
	/**
	 * Set this to false to disable DISTINCT call
	 *
	 * @var boolean
	 */
	public $distinct = true;

	//----------------------------------------------------------------------------------------- toSql
	/**
	 * Returns the Dao function as SQL
	 *
	 * @example COUNT(DISTINCT t1.`keywords`) AS `keywords`
	 * @param $builder       With_Build_Column the sql query builder
	 * @param $property_path string the property path
	 * @return string
	 */
	public function toSql(With_Build_Column $builder, $property_path)
	{
		$distinct = $this->distinct ? 'DISTINCT ' : '';
		$sql = 'COUNT(' . $distinct . $builder->buildColumn($property_path, false) . ')'
			. $this->aliasSql($builder, $property_path);
		return $sql;
	}

}
