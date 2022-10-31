<?php
namespace ITRocks\Framework\Dao\Func;

use ITRocks\Framework\Feature\List_\Summary_Builder;
use ITRocks\Framework\Sql\Builder;

/**
 * This function allow to set a raw SQL expression into a where expression
 */
class Sql implements Where
{
	use Has_To_String;

	//------------------------------------------------------------------------------------------ $sql
	/**
	 * @var string
	 */
	protected string $sql;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $sql string
	 */
	public function __construct(string $sql)
	{
		$this->sql = $sql;
	}

	//--------------------------------------------------------------------------------------- toHuman
	/**
	 * @param $builder       Summary_Builder the sql query builder
	 * @param $property_path string the property path
	 * @param $prefix        string column name prefix
	 * @return string
	 */
	public function toHuman(Summary_Builder $builder, string $property_path, string $prefix = '')
		: string
	{
		return '';
	}

	//----------------------------------------------------------------------------------------- toSql
	/**
	 * @param $builder       Builder\Where the sql query builder
	 * @param $property_path string the property path
	 * @param $prefix        string column name prefix
	 * @return string
	 */
	public function toSql(Builder\Where $builder, string $property_path, string $prefix = '') : string
	{
		return $this->sql;
	}

}
