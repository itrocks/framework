<?php
namespace SAF\Framework\Dao\Func;

use SAF\Framework\Sql\Builder;

/**
 * Dao AND function
 */
class Logical implements Where, Negate
{

	const AND_OPERATOR = ' AND ';
	const OR_OPERATOR  = ' OR ';
	const XOR_OPERATOR = ' XOR ';
	const NOT_OPERATOR = ' NOT ';
	const TRUE_OPERATOR  = ''; // to be able to negate the NOT !

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
	 * @throws \Exception
	 */
	public function __construct($operator = null, $arguments = null)
	{
		if (isset($operator))  $this->operator  = $operator;
		if (isset($arguments)) $this->arguments = $arguments;
		if (
			in_array($this->operator, [self::NOT_OPERATOR, self::TRUE_OPERATOR]) &&
			is_array($this->arguments)
		) {
			throw new \Exception("Can not build logical not|true expression with array");
		}
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

	//----------------------------------------------------------------------------------------- isNot
	/**
	 * @return boolean
	 */
	public function isNot()
	{
		return $this->operator === self::NOT_OPERATOR;
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
		//$this->arguments may not be array if operator is NOT_OPERATOR or TRUE_OPERATOR
		$arguments =  (!is_array($this->arguments) ? [$this->arguments] : $this->arguments);
		foreach ($arguments as $other_property_path => $argument) {
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
		return ($this->operator == self::NOT_OPERATOR ? 'NOT ' : '') . '(' . $sql . ')';
	}

	//------------------------------------------------------------------------------- negateArguments
	/**
	 * Negate the arguments of this
	 *
	 * @return void
	 */
	private function negateArguments()
	{
		if ($this->arguments instanceof Negate) {
			$this->arguments->negate();
		}
		elseif (is_array($this->arguments)) {
			foreach ($this->arguments as &$argument) {
				if ($argument instanceof Negate) {
					$argument->negate();
				} else {
					$argument = new Logical(self::NOT_OPERATOR, $argument);
				}
			}
		}
	}

	//---------------------------------------------------------------------------------------- negate
	/**
	 * Negate the Dao function
	 *
	 * @return void
	 */
	public function negate()
	{
		switch ($this->operator) {
			case self::AND_OPERATOR:
				$this->operator = self::OR_OPERATOR;
				$this->negateArguments();
				break;
			case self::OR_OPERATOR:
				$this->operator = self::AND_OPERATOR;
				$this->negateArguments();
				break;
			case self::XOR_OPERATOR:
				$this->operator = self::NOT_OPERATOR;
				$this->arguments = new Logical(self::XOR_OPERATOR, $this->arguments);
				break;
			case self::NOT_OPERATOR:
				$this->operator = self::TRUE_OPERATOR;
				break;
			case self::TRUE_OPERATOR:
				$this->operator = self::NOT_OPERATOR;
				break;
		}
	}

}
