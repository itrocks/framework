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
	public function getColumns() : array;

	//-------------------------------------------------------------------------------- getForeignKeys
	/**
	 * @return Foreign_Key[]
	 */
	public function getForeignKeys() : array;

	//------------------------------------------------------------------------------------ getIndexes
	/**
	 * @return Index[]
	 */
	public function getIndexes() : array;

	//--------------------------------------------------------------------------------------- getName
	/**
	 * @return string
	 */
	public function getName() : string;

	//--------------------------------------------------------------------------------- getSqlPostfix
	/**
	 * @return string
	 */
	public function getSqlPostfix() : string;

}
