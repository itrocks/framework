<?php
namespace ITRocks\Framework\Dao\Sql;

/**
 * A common interface for Dao key object representation
 */
interface Key
{

	//----------------------------------------------------------------------------------------- equiv
	/**
	 * Returns true if the key is an equivalent of the other key
	 *
	 * @param $key Key
	 * @return bool
	 */
	public function equiv(Key $key) : bool;

	//--------------------------------------------------------------------------------------- getName
	/**
	 * Gets the field name
	 *
	 * @return string
	 */
	public function getName() : string;

	//------------------------------------------------------------------------------------ getSqlType
	/**
	 * Gets the SQL version of the type
	 *
	 * @return string
	 */
	public function getSqlType() : string;

	//----------------------------------------------------------------------------------------- toSql
	/**
	 * Gets the SQL version of the column definition
	 *
	 * @return string
	 */
	public function toSql() : string;

	//--------------------------------------------------------------------------------------- getType
	/**
	 * Gets the type for the field
	 *
	 * @return string
	 */
	public function getType() : string;

}
