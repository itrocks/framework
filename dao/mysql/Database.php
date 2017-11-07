<?php
namespace ITRocks\Framework\Dao\Mysql;

use ITRocks\Framework\Dao\Sql;

/**
 * Mysql database
 */
class Database implements Sql\Database
{

	//--------------------------------------------------------------------------------- CHARACTER_SET
	/**
	 * Default character set for mysql databases
	 */
	const CHARACTER_SET = 'utf8';

	//--------------------------------------------------------------------------------------- COLLATE
	/**
	 * Default collate for mysql databases
	 */
	const COLLATE = 'utf8_general_ci';

	//------------------------------------------------------------------------------------- $Database
	/**
	 * @var string
	 */
	private $Database;

	//------------------------------------------------------------------------ characterSetCollateSql
	/**
	 * @return string @example CHARACTER SET utf8 COLLATE utf8_general_ci
	 */
	public static function characterSetCollateSql()
	{
		return static::characterSetSql() . SP . static::collateSql();
	}

	//------------------------------------------------------------------------------- characterSetSql
	/**
	 * @return string @example CHARACTER SET utf8
	 */
	public static function characterSetSql()
	{
		return 'CHARACTER SET ' . static::CHARACTER_SET;
	}

	//------------------------------------------------------------------------------------ collateSql
	/**
	 * @return string @example COLLATE utf8_general_ci
	 */
	public static function collateSql()
	{
		return 'COLLATE ' . static::COLLATE;
	}

	//--------------------------------------------------------------------------------------- getName
	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->Database;
	}

}
