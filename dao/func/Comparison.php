<?php
namespace SAF\Framework\Dao\Func;

use SAF\Framework\Sql\Builder;
use SAF\Framework\Sql\Value;

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
		if (isset($sign))       $this->sign = $sign;
		if (isset($than_value)) $this->than_value = $than_value;
		if (isset($this->than_value) && !isset($this->sign)) {
			$this->sign =
				((strpos($this->than_value, '_') !== false) || (strpos($this->than_value, '%') !== false))
					? self::LIKE
					: self::EQUAL;
		}
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
		$column = $builder->buildColumn($property_path, $prefix);
		if (is_null($this->than_value)) {
			switch ($this->sign) {
				case self::EQUAL:     case self::LIKE:     return $column . ' IS NULL';
				case self::NOT_EQUAL: case self::NOT_LIKE: return $column . ' IS NOT NULL';
			}
		}
		if ($this->than_value instanceof Where) {
			if ($this->sign == self::NOT_EQUAL) {
				return 'NOT (' . $this->than_value->toSql($builder, $property_path, $prefix) . ')';
			}
			elseif ($this->sign == self::EQUAL) {
				//Because of Negate, we should support EQUAL for instance of Where
				return ' (' . $this->than_value->toSql($builder, $property_path, $prefix) . ')';
			}
			else {
				return $this->than_value->toSql($builder, $property_path, $prefix);
			}
		}
		return $column . SP . $this->sign . SP
		. Value::escape($this->than_value/*, strpos($this->sign, 'LIKE') !== false*/);
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

}
