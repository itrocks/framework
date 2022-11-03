<?php
namespace ITRocks\Framework\Dao\Func;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Feature\List_\Summary_Builder;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Sql\Builder;
use ITRocks\Framework\Sql\Join\Sub_Query;

/**
 * Is greatest is a condition used to get the record where the column has the greatest value
 */
class Is_Greatest implements Where_Inner
{
	use Has_To_String;

	//----------------------------------------------------------------------------------- $properties
	/**
	 * @var string[]
	 */
	public array $properties;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $properties string[]|null
	 */
	public function __construct(array $properties = null)
	{
		if (isset($properties)) $this->properties = $properties;
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
		return SP . Loc::tr('is greatest of') . SP . '(' . implode(', ', $this->properties) . ')';
	}

	//----------------------------------------------------------------------------------------- toSql
	/**
	 * Returns the Dao function as SQL
	 *
	 * @param $builder       Builder\Where the sql query builder
	 * @param $property_path string the property path
	 * @param $prefix        string
	 * @return string
	 */
	public function toSql(Builder\Where $builder, string $property_path, string $prefix = '') : string
	{
		$joins = $builder->getJoins();
		// sub-query
		$class_name  = $joins->getStartingClassName();
		$properties  = $this->properties + [$property_path => Func::max()];
		$sub_builder = new Builder\Select(
			$class_name, $properties, null, $builder->getSqlLink(), [Dao::groupBy($this->properties)]
		);
		// join
		$join = new Sub_Query($sub_builder);
		$joins->addJoin($join);
		// where
		$where = '';
		foreach (array_merge($this->properties, [$property_path]) as $property) {
			$where .= ' AND '
				. $join->foreign_alias . DOT . BQ . rLastParse($property, DOT, 1, true) . BQ
				. ' = ' . $builder->buildWhereColumn($property, $prefix);
		}
		$join->where = substr($where, 5);
		return '';
	}

}
