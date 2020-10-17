<?php
namespace ITRocks\Framework\Dao\Func;

use ITRocks\Framework\Sql\Builder\With_Build_Column;

/**
 * Gets the trimester of a date (1-4)
 */
class Trimester extends Column
{

	//----------------------------------------------------------------------------------------- toSql
	/**
	 * Returns the Dao function as SQL
	 *
	 * @param $builder       With_Build_Column the sql query builder
	 * @param $property_path string the property path
	 * @return string
	 */
	public function toSql(With_Build_Column $builder, $property_path)
	{
		return 'FLOOR((MONTH(' . $builder->buildColumn($property_path, false) . ') + 2) / 3)'
			. $this->aliasSql($builder, $property_path);
	}

}
