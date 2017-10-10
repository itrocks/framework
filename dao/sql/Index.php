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
	public function getKeys();

	//--------------------------------------------------------------------------------------- getName
	/**
	 * @return string
	 */
	public function getName();

	//----------------------------------------------------------------------------------------- toSql
	/**
	 * Gets the SQL version of the column definition
	 *
	 * @returns string
	 */
	public function toSql();

}
