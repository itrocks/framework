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
		$column_names = array();
		foreach ($this->keys as $key) {
			$column_names[] = $key->toSql();
		}
		return "`" . $this->getName() . "` "
			. $this->getSqlType() . " "
			. "(" . join(", ", $column_names) . ")";
	}

}
