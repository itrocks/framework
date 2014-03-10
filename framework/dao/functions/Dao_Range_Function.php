<?php
namespace SAF\Framework;

class Dao_Range_Function implements Dao_Where_Function
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
	 * @param $builder       Sql_Where_Builder the sql query builder
	 * @param $property_path string the property path
	 * @return string
	 */
	public function toSql(Sql_Where_Builder $builder, $property_path)
	{
		return '('
			. $builder->buildColumn($property_path) . ' BETWEEN '
			. Sql_Value::escape($this->from) . ' AND ' . Sql_Value::escape($this->to)
		. ')';
	}

}
