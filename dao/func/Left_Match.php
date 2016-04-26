<?php
namespace SAF\Framework\Dao\Func;

use SAF\Framework\Sql\Builder;
use SAF\Framework\Sql\Value;

/**
 * Dao Left_Match function
 */
class Left_Match implements Where, Negate
{

	//------------------------------------------------------------------------------------ $not_match
	/**
	 * If true, then this is a 'not match' instead of a 'match'
	 *
	 * @var boolean
	 */
	public $not_match;

	//---------------------------------------------------------------------------------------- $value
	/**
	 * @var mixed|Where
	 */
	public $value;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value mixed|Where
	 * @param $not_match boolean
	 */
	public function __construct($value, $not_match = false)
	{
		$this->value = $value;
		if (isset($not_match)) $this->not_match = $not_match;
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
		$value  = Value::escape($this->value);
		return $column . ($this->not_match ? ' <> ' : ' = ') . 'LEFT(' . $value . ', LENGTH(' . $column . '))';
	}

	//---------------------------------------------------------------------------------------- negate
	/**
	 * Negate the Dao function
	 *
	 * @return void
	 */
	public function negate()
	{
		$this->not_match = !$this->not_match;
	}

}
