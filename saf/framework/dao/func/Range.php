<?php
namespace SAF\Framework\Dao\Func;

use SAF\Framework\Sql\Builder;
use SAF\Framework\Sql\Value;

/**
 * Dao Range function
 */
class Range implements Where
{

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
	 */
	public function __construct($from, $to)
	{
		$this->from = $from;
		$this->to   = $to;
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
			. $builder->buildColumn($property_path, $prefix) . ' BETWEEN '
			. Value::escape($this->from) . ' AND ' . Value::escape($this->to)
		. ')';
	}

}
