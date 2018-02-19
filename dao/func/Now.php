<?php
namespace ITRocks\Framework\Dao\Func;

use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Sql\Builder\With_Build_Column;

/**
 * A very simple function to get / compare with the actual date-time
 */
class Now extends Column
{

	//--------------------------------------------------------------------------------------- toHuman
	/**
	 * @return string
	 */
	public function toHuman()
	{
		return Loc::tr('now');
	}

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
		return 'NOW()';
	}

}
