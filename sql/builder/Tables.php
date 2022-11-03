<?php
namespace ITRocks\Framework\Sql\Builder;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Sql\Join\Joins;

/**
 * The SQL tables section (joins) of SQL queries builder
 */
class Tables
{
	use Has_Joins;

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string
	 */
	private string $class_name;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Construct the SQL 'FROM' tables list section of a query
	 *
	 * @param $class_name string
	 * @param $joins      Joins
	 */
	public function __construct(string $class_name, Joins $joins)
	{
		$this->class_name = $class_name;
		$this->joins      = $joins;
	}

	//----------------------------------------------------------------------------------------- build
	/**
	 * Build SQL tables list, based on calculated joins for where array properties paths
	 *
	 * @return string
	 */
	public function build() : string
	{
		$alias  = $this->joins->rootAlias();
		$tables = BQ . Dao::current()->storeNameOf($this->class_name) . BQ . SP . $alias;
		foreach ($this->joins->getJoins() as $join) if ($join) {
			$tables .= $join->toSql();
		}
		return $tables;
	}

	//-------------------------------------------------------------------------------------- getJoins
	/**
	 * @return Joins
	 */
	public function getJoins() : Joins
	{
		return $this->joins;
	}

}
