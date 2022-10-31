<?php
namespace ITRocks\Framework\Dao\Func;

use ITRocks\Framework\Sql;
use ITRocks\Framework\Sql\Builder\With_Build_Column;

/**
 * a value function : it always returns the value
 * Can be used as replacement for a property name for returned column values (eg select)
 */
class Value extends Column
{

	//---------------------------------------------------------------------------------------- $value
	/**
	 * @var mixed
	 */
	public mixed $value;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value mixed
	 */
	public function __construct(mixed $value)
	{
		$this->value = $value;
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
		return (is_null($this->value) ? 'NULL' : Sql\Value::escape($this->value))
			. $this->aliasSql($builder, $property_path);
	}

}
