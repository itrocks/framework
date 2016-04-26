<?php
namespace SAF\Framework\Dao\Func;

use SAF\Framework\Sql\Builder;
use SAF\Framework\Sql\Value;

/**
 * Dao Range function
 */
class Range implements Where, Negate
{

	//---------------------------------------------------------------------------------- $not_between
	/**
	 * If true, then this is a 'NOT BETWEEN' instead of a 'BETWEEN'
	 *
	 * @var boolean
	 */
	public $not_between;

	//----------------------------------------------------------------------------------------- $from
	/**
	 * @var mixed
	 */
	public $from;

	//------------------------------------------------------------------------------------------- $to
	/**
	 * @var mixed
	 */
	public $to;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $from mixed
	 * @param $to   mixed
	 * @param $not_between boolean
	 */
	public function __construct($from, $to, $not_between = false)
	{
		$this->from = $from;
		$this->to   = $to;
		if (isset($not_between)) $this->not_between = $not_between;
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
		return '('
		. $builder->buildColumn($property_path, $prefix) . ($this->not_between ? ' NOT' : '')
		. ' BETWEEN '
		// make SQL secure if given from>to
		. 'LEAST(' . Value::escape($this->from) . ',' . Value::escape($this->to) . ') '
		. ' AND '
		. 'GREATEST(' . Value::escape($this->from) . ',' . Value::escape($this->to) . ') '
		. ')';
	}

	//---------------------------------------------------------------------------------------- negate
	/**
	 * Negate the Dao function
	 *
	 * @return void
	 */
	public function negate() {
		$this->not_between = !$this->not_between;
	}

}
