<?php
namespace SAF\Framework\Dao\Func;

use SAF\Framework\Locale\Loc;
use SAF\Framework\Sql\Builder;
use SAF\Framework\Sql\Value;
use SAF\Framework\Widget\Data_List\Summary_Builder;

/**
 * Dao Range function
 */
class Range implements Negate, Where
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
		$str = '(' . $builder->buildColumn($property_path, $prefix);

		$from = $builder->buildScalar($this->from, $property_path);
		$to = $builder->buildScalar($this->to, $property_path);
		if ($from == $to) {
			$str .= SP . ($this->not_between ? Loc::tr('is not') : Loc::tr('is'));
			$str .= SP . $from . ')';
		}
		else {
			$str .= SP . ($this->not_between ? Loc::tr('is not between') : Loc::tr('is between'));
			$str .= SP . $from . SP . Loc::tr('and') . SP . $to . ')';
		}
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
		return '('
		. $builder->buildColumn($property_path, $prefix) . ($this->not_between ? ' NOT' : '')
		. ' BETWEEN '
		// make SQL secure if given from > to (if from is the greatest, then this will work too)
		. 'LEAST(' . Value::escape($this->from) . ', ' . Value::escape($this->to) . ') '
		. ' AND '
		. 'GREATEST(' . Value::escape($this->from) . ', ' . Value::escape($this->to) . ') '
		. ')';
	}

	//---------------------------------------------------------------------------------------- negate
	/**
	 * Negate the Dao function
	 */
	public function negate()
	{
		$this->not_between = !$this->not_between;
	}

}
