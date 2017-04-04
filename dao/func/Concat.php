<?php
namespace ITRocks\Framework\Dao\Func;

use ITRocks\Framework\Sql\Builder;
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
	public $columns;

	//------------------------------------------------------------------------------------ $separator
	/**
	 * @var string
	 */
	public $separator = SP;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $columns string[]
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
	 * @param $builder       Builder\Columns the sql query builder
	 * @param $property_path string the property path
	 * @return string
	 */
	public function toSql(Builder\Columns $builder, $property_path)
	{
		$columns = [];
		foreach ($this->columns as $column) {
			$columns[] = $builder->buildColumn($column, false, true);
		}
		if (count($columns) == 1) {
			$sql = reset($columns);
		}
		else {
			$separator = $this->separator ? (', ' . Value::escape($this->separator) . ', ') : ', ';
			$sql       = 'CONCAT(' . join($separator, $columns) . ')';
			if (!empty($property_path)) {
				$sql .= $this->aliasSql($builder, $property_path);
			}
		}
		return $sql;
	}

}
