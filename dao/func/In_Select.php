<?php
namespace ITRocks\Framework\Dao\Func;

use ITRocks\Framework\Feature\List_\Summary_Builder;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Sql\Builder;
use ITRocks\Framework\Sql\Builder\Select;

/**
 * Dao IN function
 */
class In_Select implements Negate, Where
{
	use Has_To_String;

	//--------------------------------------------------------------------------------------- $not_in
	/**
	 * If true, then this is a 'NOT IN' instead of a 'IN'
	 *
	 * @var boolean
	 */
	public bool $in;

	//--------------------------------------------------------------------------------------- $select
	/**
	 * @var Select
	 */
	public Select $select;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $select ?Select
	 * @param $in     boolean|null
	 */
	public function __construct(?Select $select, bool $in = null)
	{
		if (isset($select)) $this->select = $select;
		if (isset($in))     $this->in     = $in;
	}

	//---------------------------------------------------------------------------------------- negate
	/**
	 * Negate the Dao function
	 */
	public function negate()
	{
		$this->in = !$this->in;
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
	public function toHuman(Summary_Builder $builder, string $property_path, string $prefix = '')
		: string
	{
		$str = $builder->buildColumn($property_path, $prefix)
			. ($this->in ? '' : (SP . Loc::tr('except'))) . SP . Loc::tr('in') . ' (';
		$summary_builder = new Summary_Builder(
			$this->select->getClassName(), $this->select->getWhereArray()
		);
		$str .=  SP . $summary_builder;
		$str .= ')';
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
	public function toSql(Builder\Where $builder, string $property_path, string $prefix = '') : string
	{
		$sql = $builder->buildWhereColumn($property_path, $prefix)
			. ($this->in ? '' : ' NOT') . ' IN (';
		$sql .= $this->select->buildQuery();
		$sql .= ')';
		return $sql;
	}

}
