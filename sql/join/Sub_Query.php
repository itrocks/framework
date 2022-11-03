<?php
namespace ITRocks\Framework\Sql\Join;

use ITRocks\Framework\Sql\Builder;
use ITRocks\Framework\Sql\Join;

/**
 * Sql sub-query join
 */
class Sub_Query extends Join
{

	//---------------------------------------------------------------------------------------- $query
	/**
	 * @var Builder\Select|string
	 */
	public Builder\Select|string $query;

	//---------------------------------------------------------------------------------------- $where
	/**
	 * @var Builder\Where|string
	 */
	public Builder\Where|string $where;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $query Builder\Select|string|null
	 * @param $where Builder\Where|string|null
	 */
	public function __construct(
		Builder\Select|string $query = null, Builder\Where|string $where = null
	) {
		if (isset($query)) $this->query = $query;
		if (isset($where)) $this->where = $where;
	}

	//----------------------------------------------------------------------------------------- toSql
	/**
	 * @return string
	 */
	public function toSql() : string
	{
		return LF . 'INNER JOIN (' . $this->query . ') ' . $this->foreign_alias
			. ' ON ' . $this->where;
	}

}
