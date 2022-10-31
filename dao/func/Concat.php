<?php
namespace ITRocks\Framework\Dao\Func;

use ITRocks\Framework\Sql\Builder\With_Build_Column;
use ITRocks\Framework\Sql\Value;

/**
 * Concat function to group multiple values
 */
class Concat extends Column
{

	//-------------------------------------------------------------------------------------- $columns
	/**
	 * @var string[]
	 */
	public array $columns;

	//------------------------------------------------------------------------------------ $separator
	/**
	 * @var string
	 */
	public string $separator = SP;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $columns string[]|null
	 */
	public function __construct(array $columns = null)
	{
		if (isset($columns)) {
			$this->columns = $columns;
		}
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
		$columns = [];
		foreach ($this->columns as $column) {
			$columns[] = $builder->buildColumn($column, false, true);
		}
		if (count($columns) === 1) {
			$sql = reset($columns);
		}
		else {
			$separator = $this->separator ? (', ' . Value::escape($this->separator) . ', ') : ', ';
			$sql       = 'CONCAT(' . join($separator, $columns) . ')';
			if ($property_path) {
				$sql .= $this->aliasSql($builder, $property_path);
			}
		}
		return $sql;
	}

}
