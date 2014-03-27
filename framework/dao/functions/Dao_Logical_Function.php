<?php
namespace SAF\Framework;

/**
 * Dao AND function
 */
class Dao_Logical_Function implements Dao_Where_Function
{

	const AND_OPERATOR = ' AND ';
	const OR_OPERATOR  = ' OR ';
	const XOR_OPERATOR = ' XOR ';

	//------------------------------------------------------------------------------------ $arguments
	/**
	 * Key can be a property path or numeric if depends on main property part
	 *
	 * @var Dao_Where_Function[]|mixed[]
	 */
	public $arguments;

	//------------------------------------------------------------------------------------- $operator
	/**
	 * @var string
	 */
	public $operator = Dao_Logical_Function::AND_OPERATOR;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $operator string
	 * @param $arguments Dao_Where_Function[]|mixed[] key can be a property path or numeric if depends
	 * on main property part
	 */
	public function __construct($operator = null, $arguments = null)
	{
		if (isset($operator))  $this->operator  = $operator;
		if (isset($arguments)) $this->arguments = $arguments;
	}

	//----------------------------------------------------------------------------------------- isAnd
	/**
	 * @return boolean
	 */
	public function isAnd()
	{
		return $this->operator === self::AND_OPERATOR;
	}

	//------------------------------------------------------------------------------------------ isOr
	/**
	 * @return boolean
	 */
	public function isOr()
	{
		return $this->operator === self::OR_OPERATOR;
	}

	//----------------------------------------------------------------------------------------- isXor
	/**
	 * @return boolean
	 */
	public function isXor()
	{
		return $this->operator === self::XOR_OPERATOR;
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
		$sql = '';
		foreach ($this->arguments as $other_property_path => $argument) {
			if (empty($not_first)) {
				$not_first = true;
			}
			else {
				$sql .= $this->operator;
			}
			if (is_numeric($other_property_path)) {
				$sql .= ($argument instanceof Dao_Where_Function)
					? $argument->toSql($builder, $property_path)
					: Sql_Value::escape($argument);
			}
			else {
				$sql .= ($argument instanceof Dao_Where_Function)
					? $argument->toSql($builder, $other_property_path)
					: (new Dao_Comparison_Function(Dao_Comparison_Function::AUTO, $argument))->toSql(
						$builder, $other_property_path
					);
			}
		}
		return $sql;
	}

}
