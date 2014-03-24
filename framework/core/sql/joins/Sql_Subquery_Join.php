<?php
namespace SAF\Framework;

/**
 * Sql subquery join
 */
class Sql_Subquery_Join extends Sql_Join
{

	//---------------------------------------------------------------------------------------- $query
	/**
	 * @var Sql_Select_Builder|string
	 */
	public $query;

	//---------------------------------------------------------------------------------------- $where
	/**
	 * @var Sql_Where_Builder|string
	 */
	public $where;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $query Sql_Select_Builder|string
	 * @param $where Sql_Where_Builder|string
	 */
	public function __construct($query = null, $where = null)
	{
		if (isset($query)) $this->query = $query;
		if (isset($where)) $this->where = $where;
		if (!isset($this->mode)) $this->mode = Sql_Join::INNER;
	}

	//----------------------------------------------------------------------------------------- toSql
	/**
	 * @return string
	 */
	public function toSql()
	{
		return ' INNER JOIN (' . strval($this->query) . ') ' . $this->foreign_alias
			. ' ON ' . strval($this->where);
	}

}
