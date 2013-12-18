<?php
namespace SAF\Framework;

/**
 * Dao_Left_Match_Function
 */
class Dao_Left_Match_Function implements Dao_Where_Function
{

	//---------------------------------------------------------------------------------------- $value
	/**
	 * @var mixed|Dao_Where_Function
	 */
	public $value;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value mixed|Dao_Where_Function
	 */
	public function __construct($value)
	{
		$this->value = $value;
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
		$column = $builder->buildColumn($property_path);
		$value  = Sql_Value::escape($this->value);
		return $column . " = LEFT(" . $value . ", LENGTH(" . $column . "))";
	}

}
