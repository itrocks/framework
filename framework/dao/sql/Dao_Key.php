<?php
namespace SAF\Framework;

/**
 * A common class for Dao key object representation
 */
interface Dao_Key extends Field
{

	//----------------------------------------------------------------------------------------- equiv
	/**
	 * Returns true if the key is an equivalent of the other key
	 *
	 * @param $key Dao_Key
	 */
	public function equiv($key);

	//------------------------------------------------------------------------------------ getSqlType
	/**
	 * Gets the SQL version of the type
	 *
	 * @return string
	 */
	public function getSqlType();

	//----------------------------------------------------------------------------------------- toSql
	/**
	 * Gets the SQL version of the column definition
	 *
	 * @return string
	 */
	public function toSql();

}
