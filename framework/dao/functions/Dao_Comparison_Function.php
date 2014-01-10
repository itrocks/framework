<?php
namespace SAF\Framework;

/**
 * Lesser than is a condition used to get the record where the column has a value lesser than the
 * given value
 */
class Dao_Comparison_Function implements Dao_Where_Function
{

	const EQUAL = "=";
	const GREATER = ">";
	const GREATER_OR_EQUAL = ">=";
	const LESS = "<";
	const LESS_OR_EQUAL = "<=";
	const LIKE = "LIKE";
	const NOT_EQUAL = "<>";

	//----------------------------------------------------------------------------------------- $sign
	/**
	 * @var string
	 */
	public $sign = Dao_Comparison_Function::EQUAL;

	//----------------------------------------------------------------------------------- $than_value
	/**
	 * @var mixed
	 */
	public $than_value;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $sign       string
	 * @param $than_value mixed
	 */
	public function __construct($sign = null, $than_value = null)
	{
		if (isset($sign))       $this->sign = $sign;
		if (isset($than_value)) $this->than_value = $than_value;
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
		return $column . " " . $this->sign . " " . Sql_Value::escape($this->than_value);
	}

}
