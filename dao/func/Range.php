<?php
namespace ITRocks\Framework\Dao\Func;

use ITRocks\Framework\Feature\List_\Summary_Builder;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Sql\Builder;
use ITRocks\Framework\Sql\Value;

/**
 * Dao Range function
 */
class Range implements Negate, Where
{
	use Has_To_String;

	//----------------------------------------------------------------------------------------- $from
	/**
	 * @var float|int|string
	 */
	public float|int|string $from;

	//---------------------------------------------------------------------------------- $not_between
	/**
	 * If true, then this is a 'NOT BETWEEN' instead of a 'BETWEEN'
	 *
	 * @var boolean
	 */
	public bool $between;

	//------------------------------------------------------------------------------------------- $to
	/**
	 * @var float|int|string
	 */
	public float|int|string $to;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $from    mixed
	 * @param $to      mixed
	 * @param $between boolean
	 */
	public function __construct(float|int|string $from, float|int|string $to, bool $between = true)
	{
		$this->from    = $from;
		$this->to      = $to;
		$this->between = $between;
	}

	//---------------------------------------------------------------------------------------- negate
	/**
	 * Negate the Dao function
	 */
	public function negate()
	{
		$this->between = !$this->between;
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
		$str  = $builder->buildColumn($property_path, $prefix);
		$from = $builder->buildScalar($this->from, $property_path);
		$to   = $builder->buildScalar($this->to, $property_path);

		$property = $builder->getProperty($property_path);
		if ($property->getType()->isDateTime()) {
			[$date_from, $time_from] = explode(SP, $from);
			[$date_to, $time_to]     = explode(SP, $to);
			// if we check full day, we remove time parts
			if (($time_from === '00:00:00') && ($time_to === '23:59:59')) {
				$from = $date_from;
				$to   = $date_to;
			}
			else {
				// if we check full minute or full hour, we remove seconds
				$time_parts_from = explode(':', $time_from);
				$time_parts_to   = explode(':', $time_to);
				if (
					($time_parts_from[0] === $time_parts_to[0])
					&& (
						($time_parts_from[1] === $time_parts_to[1])
						|| (($time_parts_from[1] === '00') && ($time_parts_to[1] === '59'))
					)
					&& ($time_parts_from[2] === '00')
					&& ($time_parts_to[2]   === '59')
				) {
					unset($time_parts_from[2]);
					unset($time_parts_to[2]);
				}
				$time_from = implode(':', $time_parts_from);
				$time_to   = implode(':', $time_parts_to);
				$from      = trim("$date_from $time_from");
				$to        = trim("$date_to $time_to");
			}
		}

		if ($from === $to) {
			$str .= SP . ($this->between ? '=' : Loc::tr('is not')) . SP . $from;
		}
		else {
			$str = '(' . $str . SP
				. ($this->between ? Loc::tr('is between') : Loc::tr('is not between'))
				. SP . $from . SP . Loc::tr('and') . SP . $to . ')';
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
	public function toSql(Builder\Where $builder, string $property_path, string $prefix = '') : string
	{
		return '('
		. $builder->buildWhereColumn($property_path, $prefix) . ($this->between ? '' : ' NOT')
		. ' BETWEEN '
		// make SQL secure if given from > to (if from is the greatest, then this will work too)
		. 'LEAST(' . Value::escape($this->from) . ', ' . Value::escape($this->to) . ') '
		. ' AND '
		. 'GREATEST(' . Value::escape($this->from) . ', ' . Value::escape($this->to) . ') '
		. ')';
	}

}
