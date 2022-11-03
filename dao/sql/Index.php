<?php
namespace ITRocks\Framework\Dao\Sql;

/**
 * A common interface for Dao index object representation
 */
interface Index
{

	//--------------------------------------------------------------------------------------- getKeys
	/**
	 * @return Key[]
	 */
	public function getKeys() : array;

	//--------------------------------------------------------------------------------------- getName
	/**
	 * @return string
	 */
	public function getName() : string;

	//----------------------------------------------------------------------------------------- toSql
	/**
	 * Gets the SQL version of the column definition
	 *
	 * @return string
	 */
	public function toSql() : string;

}
