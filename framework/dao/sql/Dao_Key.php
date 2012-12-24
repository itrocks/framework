<?php
namespace SAF\Framework;

interface Dao_Key extends Field
{

	//----------------------------------------------------------------------------------------- equiv
	/**
	 * Returns true if the key is an equivalent of the other key
	 *
	 * @param Dao_Key $key
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
	 * @returns tring
	 */
	public function toSql();

}
