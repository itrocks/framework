<?php
namespace SAF\Framework\Dao\Func;

use SAF\Framework\Locale\Loc;
use SAF\Framework\Sql\Builder;
use SAF\Framework\Sql\Value;
use SAF\Framework\Widget\Data_List\Summary_Builder;

/**
 * Dao Left_Match function
 */
class Left_Match implements Negate, Where
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
		$replace = Loc::replace(['column' => $column, 'value' => $this->value]);
		$str = $this->not_match
			? Loc::tr('$column is not start of string "$value"', $replace)
			: Loc::tr('$column is the start of string "$value"', $replace);
		return $str;
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
		return $column
			. ($this->not_match ? ' <> ' : ' = ')
			. 'LEFT(' . $value . ', LENGTH(' . $column . '))';
	}

	//---------------------------------------------------------------------------------------- negate
	/**
	 * Negate the Dao function
	 */
	public function negate()
	{
		$this->not_match = !$this->not_match;
	}

}
