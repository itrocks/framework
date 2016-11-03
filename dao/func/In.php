<?php
namespace ITRocks\Framework\Dao\Func;

use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Sql\Builder;
use ITRocks\Framework\Sql\Value;
use ITRocks\Framework\Widget\Data_List\Summary_Builder;

/**
 * Dao IN function
 */
class In implements Negate, Where
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

	//--------------------------------------------------------------------------------------- toHuman
	/**
	 * Returns the Dao function as Human readable string
	 *
	 * @param $builder       Summary_Builder the sql query builder
	 * @param $property_path string the property path
	 * @param $prefix        string column name prefix
	 * @return string
	 */
	public function toHuman(Summary_Builder $builder, $property_path, $prefix = '')
	{
		$str = '';
		if ($this->values) {
			$str = $builder->buildColumn($property_path, $prefix)
				. ($this->not_in ? (SP . Loc::tr('except')) : '') . SP . Loc::tr('in') . ' (';
			$first = true;
			foreach ($this->values as $value) {
				if ($first) $first = false; else $str .= ', ';
				$str .= $builder->buildScalar($value, $property_path);
			}
			$str .= ')';
		}
		return $str;
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

	//---------------------------------------------------------------------------------------- negate
	/**
	 * Negate the Dao function
	 */
	public function negate()
	{
		$this->not_in = !$this->not_in;
	}

}
