<?php
namespace ITRocks\Framework\Dao\Func;

use ITRocks\Framework\Dao\Func\Logical\Exception;
use ITRocks\Framework\Feature\List_\Summary_Builder;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Sql\Builder;
use ITRocks\Framework\Tools\Names;

/**
 * Dao AND function
 */
class Logical implements Negate, Where
{

	//-------------------------------------------------------------------------- values for $operator
	const AND_OPERATOR  = ' AND ';
	const NOT_OPERATOR  = 'NOT ';
	const OR_OPERATOR   = ' OR ';
	const TRUE_OPERATOR = '';
	const XOR_OPERATOR  = ' XOR ';

	//----------------------------------------------------------------------------------------- HUMAN
	const HUMAN = [
		self::AND_OPERATOR  => 'and',
		self::NOT_OPERATOR  => 'except',
		self::OR_OPERATOR   => 'or',
		self::TRUE_OPERATOR => 'is',
		self::XOR_OPERATOR  => 'exclusively or'
	];

	//--------------------------------------------------------------------------------------- REVERSE
	const REVERSE = [
		self::AND_OPERATOR  => self::OR_OPERATOR,
		self::NOT_OPERATOR  => self::TRUE_OPERATOR,
		self::OR_OPERATOR   => self::AND_OPERATOR,
		self::TRUE_OPERATOR => self::NOT_OPERATOR,
		self::XOR_OPERATOR  => self::NOT_OPERATOR
	];

	//------------------------------------------------------------------------------------ $arguments
	/**
	 * Key can be a property path or numeric if depends on main property part
	 *
	 * @var array|Where|mixed|Where[]
	 */
	public mixed $arguments;

	//------------------------------------------------------------------------------------- $operator
	/**
	 * @var string
	 */
	public string $operator = Logical::AND_OPERATOR;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $operator string|null
	 * @param $arguments array|mixed|Where|Where[] key can be a property path or numeric if depends
	 *        on main property part
	 */
	public function __construct(string $operator = null, mixed $arguments = null)
	{
		if (isset($operator))  $this->operator  = $operator;
		if (isset($arguments)) $this->arguments = $arguments;
		if (
			in_array($this->operator, [self::NOT_OPERATOR, self::TRUE_OPERATOR], true)
			&& is_array($this->arguments)
		) {
			/** @noinspection PhpUnhandledExceptionInspection not used by intermediate programming */
			$this->throwException('Can not build logical not|true expression with array');
		}
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return Names::classToDisplay(static::class) . SP . static::HUMAN[$this->operator];
	}

	//--------------------------------------------------------------------------------- humanOperator
	/**
	 * @return string
	 */
	public function humanOperator() : string
	{
		return static::HUMAN[$this->operator];
	}

	//----------------------------------------------------------------------------------------- isAnd
	/**
	 * @return boolean
	 */
	public function isAnd() : bool
	{
		return $this->operator === self::AND_OPERATOR;
	}

	//----------------------------------------------------------------------------------------- isNot
	/**
	 * @return boolean
	 */
	public function isNot() : bool
	{
		return $this->operator === self::NOT_OPERATOR;
	}

	//------------------------------------------------------------------------------------------ isOr
	/**
	 * @return boolean
	 */
	public function isOr() : bool
	{
		return $this->operator === self::OR_OPERATOR;
	}

	//---------------------------------------------------------------------------------------- isTrue
	/**
	 * @return boolean
	 */
	public function isTrue() : bool
	{
		return $this->operator === self::TRUE_OPERATOR;
	}

	//----------------------------------------------------------------------------------------- isXor
	/**
	 * @return boolean
	 */
	public function isXor() : bool
	{
		return $this->operator === self::XOR_OPERATOR;
	}

	//---------------------------------------------------------------------------------------- negate
	/**
	 * Negate the Dao function
	 */
	public function negate()
	{
		if (!key_exists($this->operator, self::REVERSE)) {
			return;
		}
		if ($this->operator === self::XOR_OPERATOR) {
			$this->arguments = new Logical(self::XOR_OPERATOR, $this->arguments);
		}
		elseif (in_array($this->operator, [self::AND_OPERATOR, self::OR_OPERATOR], true)) {
			$this->negateArguments();
		}
		$this->operator = self::REVERSE[$this->operator];
	}

	//------------------------------------------------------------------------------- negateArguments
	/**
	 * Negate each argument of $this
	 */
	private function negateArguments()
	{
		if ($this->arguments instanceof Negate) {
			$this->arguments->negate();
		}
		elseif (!is_array($this->arguments)) {
			return;
		}
		foreach ($this->arguments as &$argument) {
			if ($argument instanceof Negate) {
				$argument->negate();
			}
			else {
				$argument = new Logical(self::NOT_OPERATOR, $argument);
			}
		}
	}

	//-------------------------------------------------------------------------------- throwException
	/**
	 * @param $message string
	 * @throws Exception
	 */
	protected function throwException(string $message)
	{
		throw new Exception($message);
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
		$str = '';
		// $this->arguments may not be array if operator is NOT_OPERATOR or TRUE_OPERATOR
		$arguments = (is_array($this->arguments) ? $this->arguments : [$this->arguments]);
		foreach ($arguments as $other_property_path => $argument) {
			if (empty($not_first)) {
				$not_first = true;
			}
			else {
				$str .= SP . Loc::tr($this->humanOperator()) . SP;
			}
			if (is_array($argument)) {
				$str .= (new Logical($this->operator, $argument))->toHuman(
					$builder,
					is_numeric($other_property_path) ? $property_path : $other_property_path,
					$prefix
				);
			}
			elseif (is_numeric($other_property_path)) {
				$str .= ($argument instanceof Where)
					? $argument->toHuman($builder, $property_path, $prefix)
					: (new Comparison(Comparison::AUTO, $argument))->toHuman(
						$builder, $property_path, $prefix
					);
			}
			else {
				$str .= ($argument instanceof Where)
					? $argument->toHuman($builder, $other_property_path, $prefix)
					: (new Comparison(Comparison::AUTO, $argument))->toHuman(
						$builder, $other_property_path, $prefix
					);
			}
		}
		return (($this->operator === self::NOT_OPERATOR) ? Loc::tr($this->humanOperator()) : '')
		. ' (' . $str . ')';
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
	public function toSql(Builder\Where $builder, string $property_path, string $prefix = '') : string
	{
		$sql = '';
		// $this->arguments may not be array if operator is NOT_OPERATOR or TRUE_OPERATOR
		$arguments = (is_array($this->arguments) ? $this->arguments : [$this->arguments]);
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
					: (new Comparison(Comparison::AUTO, $argument))
						->toSql($builder, $other_property_path, $prefix);
			}
		}
		return (($this->operator === self::NOT_OPERATOR) ? 'NOT ' : '') . '(' . $sql . ')';
	}

}
