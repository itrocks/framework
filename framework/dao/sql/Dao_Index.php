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
	 * @return Dao_Key[]
	 */
	public function getKeys();

	//----------------------------------------------------------------------------------------- toSql
	/**
	 * Gets the SQL version of the column definition
	 *
	 * @returns String
	 */
	public function toSql();

}
