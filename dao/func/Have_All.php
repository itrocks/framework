<?php
namespace ITRocks\Framework\Dao\Func;

use ITRocks\Framework\Feature\List_\Summary_Builder;
use ITRocks\Framework\Sql\Builder;

/**
 * Joined sub-object must all respect the given conditions
 */
class Have_All implements Where
{
	use Has_To_String;

	//----------------------------------------------------------------------------------- $conditions
	/**
	 * @var array|Where
	 */
	protected $conditions;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $conditions array|Where
	 */
	public function __construct($conditions)
	{
		$this->conditions = $conditions;
	}

	//--------------------------------------------------------------------------------------- toHuman
	/**
	 * @param $builder       Summary_Builder the sql query builder
	 * @param $property_path string the property path
	 * @param $prefix        string column name prefix
	 * @return string
	 */
	public function toHuman(Summary_Builder $builder, $property_path, $prefix = '')
	{
		// TODO LOWEST not used by lists yet
		return '';
	}

	//----------------------------------------------------------------------------------------- toSql
	/**
	 * @param $builder       Builder\Where the sql query builder
	 * @param $property_path string the property path
	 * @param $prefix        string column name prefix
	 * @return string
	 */
	public function toSql(Builder\Where $builder, $property_path, $prefix = '')
	{
		$sql = '';
		if ($this->conditions) {
			$alias_prefix = $builder->getJoins()->getJoin($property_path)->foreign_alias;
			foreach ($this->conditions as $condition_path => $condition) {
				$conditions[$property_path . DOT . $condition_path] = $condition;
			}
			$conditions[] = new Sql(
				$alias_prefix . 't0.id = t0.id'
			);
			$select = new Builder\Count(
				$builder->getJoins()->getStartingClassName(), $conditions, $builder->getSqlLink()
			);
			$select->getJoins()->alias_prefix = $alias_prefix;
			$select_sql = $select->buildQuery();
			$sql = (new Count)->toSql($builder, $property_path . '.id') . ' = (' . $select_sql . ')';
		}
		return $sql;
	}

}
