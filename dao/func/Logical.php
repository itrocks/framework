<?php
namespace SAF\Framework\Dao\Func;

use SAF\Framework\Sql\Builder;

/**
 * Dao AND function
 */
class Logical implements Where
{

	const AND_OPERATOR = ' AND ';
	const OR_OPERATOR  = ' OR ';
	const XOR_OPERATOR = ' XOR ';

	//------------------------------------------------------------------------------------ $arguments
	/**
	 * Key can be a property path or numeric if depends on main property part
	 *
	 * @var Where[]|mixed[]
	 */
	public $arguments;

	//------------------------------------------------------------------------------------- $operator
	/**
	 * @var string
	 */
	public $operator = Logical::AND_OPERATOR;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $operator string
	 * @param $arguments Where[]|mixed key can be a property path or numeric if depends
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
	 * @param $builder       Builder\Where the sql query builder
	 * @param $property_path string the property path
	 * @param $prefix        string column name prefix
	 * @return string
	 */
	public function toSql(Builder\Where $builder, $property_path, $prefix = '')
	{
		$sql = '';
		foreach ($this->arguments as $other_property_path => $argument) {
			if (empty($not_first)) {
				$not_first = true;
			}
			else {
				$sql .= $this->operator;
			}
			if (is_array($argument)) {
				$sql .= (new Logical($this->operator, $argument))->toSql(
					$builder,
					is_numeric($other_property_path) ? $property_path : $other_property_path,
					$prefix
				);
			}
			elseif (is_numeric($other_property_path)) {
				$sql .= ($argument instanceof Where)
					? $argument->toSql($builder, $property_path, $prefix)
					: (new Comparison(Comparison::AUTO, $argument))->toSql($builder, $property_path, $prefix);
			}
			else {
				$sql .= ($argument instanceof Where)
					? $argument->toSql($builder, $other_property_path, $prefix)
					: (new Comparison(Comparison::AUTO, $argument))->toSql($builder, $other_property_path, $prefix);
			}
		}
		return '(' . $sql . ')';
	}

}
