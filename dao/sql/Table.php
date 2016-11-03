<?php
namespace ITRocks\Framework\Dao\Sql;

/**
 * A common interface for Dao table object representation
 */
interface Table
{

	//------------------------------------------------------------------------------------ getColumns
	/**
	 * @return Column[]
	 */
	public function getColumns();

	//-------------------------------------------------------------------------------- getForeignKeys
	/**
	 * @return Foreign_Key[]
	 */
	public function getForeignKeys();

	//------------------------------------------------------------------------------------ getIndexes
	/**
	 * @return Index[]
	 */
	public function getIndexes();

	//--------------------------------------------------------------------------------------- getName
	/**
	 * @return string
	 */
	public function getName();

	//--------------------------------------------------------------------------------- getSqlPostfix
	/**
	 * @return string
	 */
	public function getSqlPostfix();

}
