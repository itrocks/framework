<?php
namespace SAF\Framework\Dao\Mysql;

use SAF\Framework\Dao\Sql;

/**
 * An object representation of a mysql index
 */
class Index implements Sql\Index
{

	//------------------------------------------------------------------------ $type values constants
	const KEY     = 'KEY';
	const PRIMARY = 'PRIMARY';
	const UNIQUE  = 'UNIQUE';

	//----------------------------------------------------------------------------------------- $keys
	/**
	 * @var Key[]
	 */
	public $keys;

	//----------------------------------------------------------------------------------------- $type
	/**
	 * @values KEY, UNIQUE
	 * @var string
	 */
	public $type = self::KEY;

	//---------------------------------------------------------------------------------------- addKey
	/**
	 * Add a key built from a given column name
	 *
	 * @param $column_name string
	 */
	public function addKey($column_name)
	{
		$this->keys[] = new Key($column_name);
	}

	//------------------------------------------------------------------------------------- buildLink
	/**
	 * Builds a Index for a column name that is a link to another class
	 *
	 * @param $column_name string the column name used to create the index (with or without 'id_')
	 * @return Index
	 */
	public static function buildLink($column_name)
	{
		if (substr($column_name, 0, 3) !== 'id_') {
			$column_name = 'id_' . $column_name;
		}
		$index = new Index();
		$index->addKey($column_name);
		return $index;
	}

	//--------------------------------------------------------------------------------------- getKeys
	/**
	 * @return Key[]
	 */
	public function getKeys()
	{
		return $this->keys;
	}

	//--------------------------------------------------------------------------------------- getName
	/**
	 * @return string
	 */
	public function getName()
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
	public function getSqlType()
	{
		return reset($this->keys)->getSqlType();
	}

	//--------------------------------------------------------------------------------------- setType
	/**
	 * @param $type string self::KEY, self::PRIMARY, self::UNIQUE
	 */
	public function setType($type)
	{
		$this->type = $type;
	}

	//----------------------------------------------------------------------------------------- toSql
	/**
	 * @return string
	 */
	public function toSql()
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
