<?php
namespace ITRocks\Framework\Dao\Func;

use ITRocks\Framework\Feature\List_\Summary_Builder;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Locale\Option\Replace;
use ITRocks\Framework\Sql\Builder;
use ITRocks\Framework\Sql\Value;

/**
 * Dao Left_Match function
 */
class Left_Match implements Negate, Where
{
	use Has_To_String;

	//------------------------------------------------------------------------------------ $not_match
	/**
	 * If true, then this is a 'not match' instead of a 'match'
	 *
	 * @var boolean
	 */
	public bool $match = true;

	//---------------------------------------------------------------------------------------- $value
	/**
	 * @var mixed|Where
	 */
	public mixed $value;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value mixed|Where
	 * @param $match boolean|null
	 */
	public function __construct(mixed $value, bool $match = null)
	{
		$this->value = $value;
		if (isset($match)) $this->match = $match;
	}

	//---------------------------------------------------------------------------------------- negate
	/**
	 * Negate the Dao function
	 */
	public function negate() : void
	{
		$this->match = !$this->match;
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
		$column  = $builder->buildColumn($property_path, $prefix);
		$replace = new Replace(['column' => $column, 'value' => $this->value]);
		return $this->match
			? Loc::tr('$column is the start of string "$value"', $replace)
			: Loc::tr('$column is not start of string "$value"', $replace);
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
		$value  = Value::escape($this->value);
		return $column
			. ($this->match ? ' = ' : ' <> ')
			. 'LEFT(' . $value . ', LENGTH(' . $column . '))';
	}

}
