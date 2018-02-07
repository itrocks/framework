<?php
namespace ITRocks\Framework\Dao\Func;

use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Sql\Builder;
use ITRocks\Framework\Sql\Builder\Select;
use ITRocks\Framework\Widget\Data_List\Summary_Builder;

/**
 * Dao IN function
 */
class InSelect implements Negate, Where
{

	//--------------------------------------------------------------------------------------- $not_in
	/**
	 * If true, then this is a 'NOT IN' instead of a 'IN'
	 *
	 * @var boolean
	 */
	public $not_in;

	//--------------------------------------------------------------------------------------- $select
	/**
	 * @var Select
	 */
	public $select;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $select Select
	 * @param $not_in boolean
	 */
	public function __construct(Select $select, $not_in = false)
	{
		if (isset($select)) $this->select = $select;
		if (isset($not_in)) $this->not_in = $not_in;
	}

	//---------------------------------------------------------------------------------------- negate
	/**
	 * Negate the Dao function
	 */
	public function negate()
	{
		$this->not_in = !$this->not_in;
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
		if ($this->select) {
			$str = $builder->buildColumn($property_path, $prefix)
				. ($this->not_in ? (SP . Loc::tr('except')) : '') . SP . Loc::tr('in') . ' (';
			$summary_builder = new Summary_Builder(
				$this->select->getClassName(), $this->select->getWhereArray()
			);
			$str .=  SP . (string)$summary_builder;
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
		if ($this->select) {
			$sql = $builder->buildWhereColumn($property_path, $prefix)
				. ($this->not_in ? ' NOT' : '') . ' IN (';
			$sql .= $this->select->buildQuery();
			$sql .= ')';
		}
		return $sql;
	}

}
