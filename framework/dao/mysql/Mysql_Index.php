<?php
namespace SAF\Framework;

/**
 * An object representation of a mysql index
 */
class Mysql_Index implements Dao_Index
{

	//----------------------------------------------------------------------------------------- $keys
	/**
	 * @var Mysql_Key[]
	 */
	public $keys;

	//------------------------------------------------------------------------------------- buildLink
	/**
	 * Builds a Mysql_Index for a column name that is a link to another class
	 *
	 * @param $column_name string the column name used to create the index (with or without 'id_')
	 * @return Mysql_Index
	 */
	public static function buildLink($column_name)
	{
		if (substr($column_name, 0, 3) !== 'id_') {
			$column_name = 'id_' . $column_name;
		}
		$index = new Mysql_Index();
		$index->keys[] = new Mysql_Key($column_name);
		return $index;
	}

	//--------------------------------------------------------------------------------------- getKeys
	/**
	 * @return Mysql_Key[]
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
		return reset($this->keys)->getName();
	}

	//------------------------------------------------------------------------------------ getSqlType
	/**
	 * @return string
	 */
	public function getSqlType()
	{
		return reset($this->keys)->getSqlType();
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
		return 'KEY `' . $this->getName() . '` '
			. ($type ? ($type . ' ') : '')
			. '(' . join(', ', $column_names) . ')';
	}

}
