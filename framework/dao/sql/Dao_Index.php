<?php
namespace SAF\Framework;

interface Dao_Index
{

	//--------------------------------------------------------------------------------------- getName
	/**
	 * @return string
	 */
	public function getName();

	//--------------------------------------------------------------------------------------- getKeys
	/**
	 * @return multitype:Dao_Key
	 */
	public function getKeys();

	//----------------------------------------------------------------------------------------- toSql
	/**
	 * Gets the SQL version of the column definition
	 *
	 * @returns tring
	 */
	public function toSql();

}
