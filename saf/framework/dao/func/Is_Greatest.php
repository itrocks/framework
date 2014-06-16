<?php
namespace SAF\Framework\Dao\Func;

use SAF\Framework\Dao\Func;
use SAF\Framework\Dao;
use SAF\Framework\Sql\Builder;
use SAF\Framework\Sql\Join\Subquery;

/**
 * Is greatest is a condition used to get the record where the column has the greatest value
 */
class Is_Greatest implements Where_Inner
{

	//----------------------------------------------------------------------------------- $properties
	/**
	 * @var string[]
	 */
	public $properties;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $properties string[]
	 */
	public function __construct($properties = null)
	{
		if (isset($properties)) $this->properties = $properties;
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
	public function toSql(Builder\Where $builder, $property_path, $prefix = '')
	{
		$joins = $builder->getJoins();
		// sub-query
		$class_name = $joins->getStartingClassName();
		$properties = $this->properties + [$property_path => Func::max()];
		$sub_builder = new Builder\Select(
			$class_name, $properties, null, $builder->getSqlLink(), [Dao::groupBy($this->properties)]
		);
		// join
		$join = new Subquery($sub_builder);
		$joins->addJoin($join);
		// where
		$where = '';
		foreach (array_merge($this->properties, [$property_path]) as $property) {
			$where .= ' AND '
				. $join->foreign_alias . DOT . BQ . rLastParse($property, DOT, 1, true) . BQ
				. ' = ' . $builder->buildColumn($property, $prefix);
		}
		$join->where = substr($where, 5);
		return null;
	}

}
