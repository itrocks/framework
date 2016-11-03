<?php
namespace ITRocks\Framework\Dao\Sql;

/**
 * A common interface for Dao index object representation
 */
interface Index
{

	//--------------------------------------------------------------------------------------- getName
	/**
	 * @return string
	 */
	public function getName();

	//--------------------------------------------------------------------------------------- getKeys
	/**
	 * @return Key[]
	 */
	public function getKeys();

	//----------------------------------------------------------------------------------------- toSql
	/**
	 * Gets the SQL version of the column definition
	 *
	 * @returns string
	 */
	public function toSql();

}
