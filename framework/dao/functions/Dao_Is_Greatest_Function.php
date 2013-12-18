<?php
namespace SAF\Framework;

/**
 * Is greatest is a condition used to get the record where the column has the greatest value
 */
class Dao_Is_Greatest_Function implements Dao_Where_Function_Inner
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
	 * @param $builder       Sql_Where_Builder the sql query builder
	 * @param $property_path string the property path
	 * @return string
	 */
	public function toSql(Sql_Where_Builder $builder, $property_path)
	{
		$joins = $builder->getJoins();
		// sub-query
		$class_name = $joins->getStartingClassName();
		$properties = $this->properties + array($property_path => Dao_Func::max());
		$sub_builder = new Sql_Select_Builder(
			$class_name, $properties, null, $builder->getSqlLink(), Dao::groupBy($this->properties)
		);
		// join
		$join = new Sql_Subquery_Join($sub_builder);
		$joins->addJoin($join);
		// where
		$where = "";
		foreach (array_merge($this->properties, array($property_path)) as $property) {
			$where .= " AND "
				. $join->foreign_alias . ".`" . rLastParse($property, ".", 1, true) . "`"
				. " = " . $builder->buildColumn($property);
		}
		$join->where = substr($where, 5);
		return null;
	}

}
