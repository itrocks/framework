<?php
namespace SAF\Framework\Sql\Join;

use SAF\Framework\Sql\Builder;
use SAF\Framework\Sql\Join;

/**
 * Sql subquery join
 */
class Subquery extends Join
{

	//---------------------------------------------------------------------------------------- $query
	/**
	 * @var Builder\Select|string
	 */
	public $query;

	//---------------------------------------------------------------------------------------- $where
	/**
	 * @var Builder\Where|string
	 */
	public $where;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $query Builder\Select|string
	 * @param $where Builder\Where|string
	 */
	public function __construct($query = null, $where = null)
	{
		if (isset($query)) $this->query = $query;
		if (isset($where)) $this->where = $where;
		if (!isset($this->mode)) $this->mode = Join::INNER;
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
