<?php
namespace SAF\Framework\Dao\Func;

use SAF\Framework\Sql\Builder;
use SAF\Framework\Sql\Value;

/**
 * Dao IN function
 */
class In implements Where
{

	//--------------------------------------------------------------------------------------- $not_in
	/**
	 * If true, then this is a 'NOT IN' instead of a 'IN'
	 *
	 * @var boolean
	 */
	public $not_in;

	//--------------------------------------------------------------------------------------- $values
	/**
	 * @var mixed[]
	 */
	public $values;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $values mixed[]
	 * @param $not_in boolean
	 */
	public function __construct($values = null, $not_in = false)
	{
		if (isset($values)) $this->values = $values;
		if (isset($not_in)) $this->not_in = $not_in;
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
			$sql = $builder->buildColumn($property_path, $prefix)
				. ($this->not_in ? ' NOT' : '') . ' IN (';
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
