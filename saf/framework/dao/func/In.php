<?php
namespace SAF\Framework\Dao\Func;

use SAF\Framework\Sql\Builder;
use SAF\Framework\Sql\Value;

/**
 * Dao IN function
 */
class In implements Where
{

	//--------------------------------------------------------------------------------------- $values
	/**
	 * @var mixed[]
	 */
	public $values;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $values mixed[]
	 */
	public function __construct($values = null)
	{
		if (isset($values)) $this->values = $values;
	}

	//----------------------------------------------------------------------------------------- toSql
	/**
	 * Returns the Dao function as SQL
	 *
	 * @param $builder       Builder\Where the sql query builder
	 * @param $property_path string the property path
	 * @param $prefix        string column name prefix
	 * @return string
	 */
	public function toSql(Builder\Where $builder, $property_path, $prefix = '')
	{
		$sql = '';
		if ($this->values) {
			$sql = $builder->buildColumn($property_path, $prefix) . ' IN (';
			$first = true;
			foreach ($this->values as $value) {
				if ($first) $first = false; else $sql .= ', ';
				$sql .= Value::escape($value);
			}
			$sql .= ')';
		}
		return $sql;
	}

}
