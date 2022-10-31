<?php
namespace ITRocks\Framework\Dao\Func;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Feature\List_\Summary_Builder;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Annotation\Property\Values_Annotation;
use ITRocks\Framework\Sql\Builder;
use ITRocks\Framework\Sql\Value;

/**
 * Lesser than is a condition used to get the record where the column has a value lesser than the
 * given value
 */
class Comparison implements Negate, Where
{

	//---------------------------------------------------------------------------------- $sign values
	const AUTO             = null;
	const EQUAL            = '=';
	const GREATER          = '>';
	const GREATER_OR_EQUAL = '>=';
	const LESS             = '<';
	const LESS_OR_EQUAL    = '<=';
	const LIKE             = 'LIKE';
	const NOT_EQUAL        = '<>';
	const NOT_LIKE         = 'NOT LIKE';

	//--------------------------------------------------------------------------------------- REVERSE
	const REVERSE = [
		self::EQUAL            => self::NOT_EQUAL,
		self::GREATER          => self::LESS_OR_EQUAL,
		self::GREATER_OR_EQUAL => self::LESS,
		self::LESS             => self::GREATER_OR_EQUAL,
		self::LESS_OR_EQUAL    => self::GREATER,
		self::LIKE             => self::NOT_LIKE,
		self::NOT_EQUAL        => self::EQUAL,
		self::NOT_LIKE         => self::LIKE
	];

	//----------------------------------------------------------------------------------------- $sign
	/**
	 * @values =, >, >=, <, <=, LIKE, <>, NOT LIKE
	 * @var string
	 */
	public string $sign;

	//----------------------------------------------------------------------------------- $than_value
	/**
	 * @null
	 * @var mixed
	 */
	public mixed $than_value;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $sign       string|null
	 * @param $than_value mixed
	 */
	public function __construct(string $sign = null, mixed $than_value = null)
	{
		if (isset($sign))       $this->sign       = $sign;
		if (isset($than_value)) $this->than_value = $than_value;
		if (isset($this->than_value) && !isset($this->sign)) {
			$this->sign = (str_contains($this->than_value, '_') || str_contains($this->than_value, '%'))
				? self::LIKE
				: self::EQUAL;
		}
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return $this->sign . SP . $this->than_value;
	}

	//---------------------------------------------------------------------------------------- negate
	/**
	 * Negate the comparison
	 *
	 * @example GREATER will become LESS_OR_EQUAL
	 */
	public function negate()
	{
		if (in_array($this->sign, self::REVERSE, true)) {
			$this->sign = self::REVERSE[$this->sign];
		}
	}

	//----------------------------------------------------------------------------------- signToHuman
	/**
	 * @param $sign string
	 * @return string
	 */
	public function signToHuman(string $sign) : string
	{
		return in_array($sign, [self::LIKE, self::NOT_LIKE], true)
			? Loc::tr('is' . SP . strtolower($sign))
			: $sign;
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
		$column = $builder->buildColumn($property_path, $prefix);
		if (is_null($this->than_value)) {
			switch ($this->sign) {
				case self::EQUAL:     case self::LIKE:     return $column . SP . Loc::tr('is empty');
				case self::NOT_EQUAL: case self::NOT_LIKE: return $column . SP . Loc::tr('is not empty');
			}
		}
		if ($this->than_value instanceof Where) {
			return $this->whereSql(
				$column,
				$this->than_value->toHuman($builder, $property_path, $prefix)
			);
		}
		$translate_flag = Summary_Builder::COMPLETE_TRANSLATE;
		// for a LIKE for property with @values, we do not translate the expression
		if (in_array($this->sign, [self::LIKE, self::NOT_LIKE], true)) {
			$property = $builder->getProperty($property_path);
			// check if we are on a enum field with @values list of values
			$values = ($property ? Values_Annotation::of($property)->values() : []);
			if ($values) {
				$translate_flag = Summary_Builder::NO_TRANSLATE;
			}
		}
		$scalar = $builder->buildScalar($this->than_value, $property_path, $translate_flag);
		if (in_array($this->sign, [self::LIKE, self::NOT_LIKE], true)) {
			$scalar = str_replace(['_', '%'], ['?', '*'], $scalar);
		}
		return $column . SP . $this->signToHuman($this->sign) . SP . $scalar;
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
		$column = $builder->buildWhereColumn($property_path, $prefix);
		if (is_null($this->than_value)) {
			$operand = in_array($this->sign, [self::EQUAL, self::LIKE], true) ? 'IS NULL' : 'IS NOT NULL';
			return $column . SP . $operand;
		}
		if ($this->than_value instanceof Where) {
			return $this->whereSql(
				$column,
				$this->than_value->toSql($builder, $property_path, $prefix)
			);
		}
		if (
			is_object($this->than_value)
			&& ($identifier = Dao::getObjectIdentifier($this->than_value, 'id'))
		) {
			return $column . SP . $this->sign . SP . $identifier;
		}
		$sql = $column . SP . $this->sign
			. SP . Value::escape($this->than_value, str_contains($this->sign, 'LIKE'));
		if (
			str_contains($property_path, DOT)
			&& in_array($this->sign, [static::NOT_EQUAL, static::NOT_LIKE], true)
		) {
			$sql = '(' . $sql . ' OR ' . $column . ' IS NULL)';
		}
		return $sql;
	}

	//-------------------------------------------------------------------------------------- whereSql
	/**
	 * Specific sql parsing in case of Where
	 *
	 * @param $column string
	 * @param $sql    string
	 * @return string
	 */
	private function whereSql(string $column, string $sql) : string
	{
		if ($this->than_value instanceof Property) {
			$sql = $column . SP . $this->sign . SP . $sql;
		}
		else {
			$sql = '(' . $sql . ')';
			if ($this->sign === self::NOT_EQUAL) {
				$sql = 'NOT ' . $sql;
			}
		}
		return $sql;
	}

}
