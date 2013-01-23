<?php
namespace SAF\Framework;

class Mysql_Index implements Dao_Index
{

	//----------------------------------------------------------------------------------------- $keys
	/**
	 * @var Mysql_Key[]
	 */
	public $keys;

	//--------------------------------------------------------------------------------------- getKeys
	public function getKeys()
	{
		return $this->keys;
	}

	//--------------------------------------------------------------------------------------- getName
	public function getName()
	{
		return reset($this->keys)->getName();
	}

	//------------------------------------------------------------------------------------ getSqlType
	public function getSqlType()
	{
		return reset($this->keys)->getSqlType();
	}

	//----------------------------------------------------------------------------------------- toSql
	public function toSql()
	{
		foreach ($this->keys as $key) {
			$column_names[] = $key->toSql();
		}
		return "`" . $this->getName() . "` "
			. $this->getSqlType() . " "
			. "(" . join(", ", $column_names) . ")";
	}

}
