<?php
namespace ITRocks\Framework\Dao\Func;

use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Annotation\Property\Values_Annotation;
use ITRocks\Framework\Reflection\Type;
use ITRocks\Framework\Sql\Builder;
use ITRocks\Framework\Sql\Value;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Widget\Data_List\Summary_Builder;

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
	public $sign;

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
		if (isset($sign))       $this->sign       = $sign;
		if (isset($than_value)) $this->than_value = $than_value;
		if (isset($this->than_value) && !isset($this->sign)) {
			$this->sign =
				((strpos($this->than_value, '_') !== false) || (strpos($this->than_value, '%') !== false))
				? self::LIKE
				: self::EQUAL;
		}
	}

	//---------------------------------------------------------------------------------------- negate
	/**
	 * Negate the comparison
	 *
	 * @example GREATER will become LESS_OR_EQUAL
	 */
	public function negate()
	{
		if (in_array($this->sign, self::REVERSE)) {
			$this->sign = self::REVERSE[$this->sign];
		}
	}

	//----------------------------------------------------------------------------------- signToHuman
	/**
	 * @param $sign string
	 * @return string
	 */
	public function signToHuman($sign)
	{
		return (
			in_array($sign, [self::LIKE, self::NOT_LIKE]) ?	Loc::tr('is' . SP . strtolower($sign)) : $sign
		);
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
	public function toHuman(Summary_Builder $builder, $property_path, $prefix = '')
	{
		$column = $builder->buildColumn($property_path, $prefix);
		if (is_null($this->than_value)) {
			switch ($this->sign) {
				case self::EQUAL:     case self::LIKE:     return $column . SP . Loc::tr('is empty');
				case self::NOT_EQUAL: case self::NOT_LIKE: return $column . SP . Loc::tr('is not empty');
			}
		}
		if ($this->than_value instanceof Where) {
			return $this->whereSQL(
				$column,
				$this->than_value->toHuman($builder, $property_path, $prefix)
			);
		}
		$translate_flag = Summary_Builder::COMPLETE_TRANSLATE;
		// for a LIKE for property with @values, we do not translate the expression
		if (in_array($this->sign, [self::LIKE, self::NOT_LIKE])) {
			$property = $builder->getProperty($property_path);
			// check if we are on a enum field with @values list of values
			$values = ($property ? Values_Annotation::of($property)->values() : []);
			if ($values) {
				$translate_flag = Summary_Builder::NO_TRANSLATE;
			}
		}
		$scalar = $builder->buildScalar($this->than_value, $property_path, $translate_flag);
		if (in_array($this->sign, [self::LIKE, self::NOT_LIKE])) {
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
	public function toSql(Builder\Where $builder, $property_path, $prefix = '')
	{
		$column = $builder->buildWhereColumn($property_path, $prefix);
		if (is_null($this->than_value)) {
			if (in_array($this->sign, [self::EQUAL, self::NOT_EQUAL, self::LIKE, self::NOT_LIKE])) {
				$close_parenthesis = '';
				switch ($this->sign) {
					case self::NOT_EQUAL:
					case self::NOT_LIKE:
						$sign    = self::NOT_EQUAL;
						$logical = 'AND';
						$operand = 'IS NOT NULL';
						break;
					default: /*case self::EQUAL: case self::LIKE:*/
						$sign    = self::EQUAL;
						$logical = 'OR';
						$operand = 'IS NULL';
						break;
				}
				$sql = '';
				// in case of Date_Time is null we want to check for '0000-00-00 00:00:00' too
				// property may be null if reverse path : Class\Name->foreign_property_name
				$property    = $builder->getProperty($property_path);
				$type_string = $property ? $property->getType()->asString() : null;
				if ($type_string == Date_Time::class) {
					$close_parenthesis = ')';
					$sql .= '(' . $column . SP . $sign . SP
						. DQ . '0000-00-00 00:00:00' . DQ . SP . $logical . SP;
				}
				// in case of numeric is null we want to check for 0 too
				elseif (in_array($type_string, [Type::BOOLEAN, Type::FLOAT, Type::INTEGER])) {
					$close_parenthesis = ')';
					$sql .= '(' . $column . SP . $sign . SP . '0' . SP . $logical . SP;
				}
				$sql .= $column . SP . $operand . $close_parenthesis;
				return $sql;
			}
		}
		if ($this->than_value instanceof Where) {
			return $this->whereSQL(
				$column,
				$this->than_value->toSql($builder, $property_path, $prefix)
			);
		}
		return $column . SP . $this->sign
		. SP . Value::escape($this->than_value, strpos($this->sign, 'LIKE') !== false);
	}

	//-------------------------------------------------------------------------------------- whereSQL
	/**
	 * Specific sql parsing in case of Where
	 *
	 * @param $column string
	 * @param $sql    string
	 * @return string
	 */
	private function whereSQL($column, $sql)
	{
		if ($this->than_value instanceof Property) {
			$sql = $column . SP . $this->sign . SP . $sql;
		}
		else {
			$sql = '(' . $sql . ')';
			if ($this->sign == self::NOT_EQUAL) {
				$sql = 'NOT ' . $sql;
			}
		}
		return $sql;
	}

}
