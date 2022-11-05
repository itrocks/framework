<?php
namespace ITRocks\Framework\Dao\Mysql;

use ITRocks\Framework\Dao\Sql;

/**
 * An object representation of a mysql index
 */
class Index implements Sql\Index
{

	//------------------------------------------------------------------------ $type values constants
	const KEY     = 'KEY';
	const PRIMARY = 'PRIMARY KEY';
	const UNIQUE  = 'UNIQUE KEY';

	//----------------------------------------------------------------------------------------- $keys
	/**
	 * @var Key[]
	 */
	public array $keys;

	//----------------------------------------------------------------------------------------- $type
	/**
	 * @values KEY, UNIQUE
	 * @var string
	 */
	public string $type = self::KEY;

	//---------------------------------------------------------------------------------------- addKey
	/**
	 * Add a key built from a given column name
	 *
	 * @param $column_name string
	 */
	public function addKey(string $column_name) : void
	{
		$this->keys[] = new Key($column_name);
	}

	//------------------------------------------------------------------------------------- buildLink
	/**
	 * Builds a Index for a column name that is a link to another class
	 *
	 * @param $column_name string the column name used to create the index (with or without 'id_')
	 * @return static
	 */
	public static function buildLink(string $column_name) : static
	{
		if (!str_starts_with($column_name, 'id_')) {
			$column_name = 'id_' . $column_name;
		}
		$index = new static();
		$index->addKey($column_name);
		return $index;
	}

	//--------------------------------------------------------------------------------------- getKeys
	/**
	 * @return Key[]
	 */
	public function getKeys() : array
	{
		return $this->keys;
	}

	//--------------------------------------------------------------------------------------- getName
	/**
	 * @return string
	 */
	public function getName() : string
	{
		$names = [];
		foreach ($this->keys as $key) {
			$names[] = $key->getName();
		}
		return join('.', $names);
	}

	//------------------------------------------------------------------------------------ getSqlType
	/**
	 * @return string
	 */
	public function getSqlType() : string
	{
		return reset($this->keys)->getSqlType();
	}

	//--------------------------------------------------------------------------------------- setType
	/**
	 * @param $type string self::KEY, self::PRIMARY, self::UNIQUE
	 */
	public function setType(string $type) : void
	{
		$this->type = $type;
	}

	//----------------------------------------------------------------------------------------- toSql
	/**
	 * @return string
	 */
	public function toSql() : string
	{
		$column_names = [];
		foreach ($this->keys as $key) {
			$column_names[] = $key->toSql();
		}
		$type = $this->getSqlType();
		return $this->type . SP . BQ . $this->getName() . BQ . SP
			. ($type ? ($type . SP) : '')
			. '(' . join(', ', $column_names) . ')';
	}

}
